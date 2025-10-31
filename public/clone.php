<?php
// proxy_clone.php
// Sử dụng: deploy lên domain của bạn, ví dụ: https://api.vietmmo.net/proxy_clone.php?u=https%3A%2F%2Flodgicli.com
// Hoặc chỉnh $DEFAULT_TARGET phía dưới.

// ---------- Cấu hình cơ bản ----------
$DEFAULT_TARGET = 'https://lodgicli.com';    // nếu không truyền ?u=... thì dùng site này
$MY_DOMAIN = $_SERVER['HTTP_HOST'];          // domain hiện tại (ví dụ: api.vietmmo.net)

// Chuỗi thay thế dạng "from|to; from2|to2;"
// Bạn có thể set bằng GET param 'repl' hoặc chỉnh trực tiếp ở đây.
$default_replacements = 'lodgicli|Logdidog; insurance|guarantee ;';

// ---------- Lấy URL đích và replacement mapping ----------
$target = isset($_GET['u']) ? urldecode($_GET['u']) : $DEFAULT_TARGET;
$repl_param = isset($_GET['repl']) ? $_GET['repl'] : $default_replacements;

// chuẩn hóa target (bắt đầu bằng http:// hoặc https://)
if (!preg_match('#^https?://#i', $target)) {
    $target = 'http://' . $target;
}

// build replacements array
$repls = [];
foreach (explode(';', $repl_param) as $part) {
    $part = trim($part);
    if ($part === '') continue;
    $pair = explode('|', $part, 2);
    if (count($pair) == 2) {
        $from = trim($pair[0]);
        $to = trim($pair[1]);
        if ($from !== '') $repls[$from] = $to;
    }
}

// ---------- cURL fetch ----------
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // chúng ta xử lý redirect thủ công
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_USERAGENT, isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'PHP Proxy');
curl_setopt($ch, CURLOPT_HTTPHEADER, buildForwardHeaders());
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // nếu cần HTTPS; production nên bật/ cấu hình cert
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
if ($response === false) {
    http_response_code(502);
    echo "Error fetching target: " . curl_error($ch);
    exit;
}
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$raw_headers = substr($response, 0, $header_size);
$body = substr($response, $header_size);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

// ---------- Parse headers ----------
$headers = parseHeaders($raw_headers);

// Nếu server gốc trả redirect (3xx) với Location, chuyển Location qua proxy
if ($http_code >= 300 && $http_code < 400 && isset($headers['Location'])) {
    $loc = $headers['Location'];
    // convert relative location to absolute
    $loc = resolveUrl($loc, $target);
    $proxyLoc = buildProxyUrl($loc, $repl_param);
    header("Location: $proxyLoc", true, $http_code);
    exit;
}

// ---------- Nếu binary (image, pdf, css, js...), forward trực tiếp (không replace nội dung text) ----------
if ($content_type !== null) {
    $ct = explode(';', $content_type)[0];
} else {
    $ct = '';
}

// Các loại text/html hoặc text/* hoặc json -> có thể xử lý replace
$text_like = preg_match('#^(text/|application/(json|javascript|xml))#i', $ct) || stripos($ct, 'html') !== false;

if ($text_like) {
    // Convert relative asset URLs -> absolute URLs based on $target
    $body = fixRelativeUrls($body, $target);

    // Rewrite absolute URLs pointing to the original host -> route lại qua proxy
    $body = rewriteLinksToProxy($body, $target);

    // Apply string replacements (simple literal replace)
    foreach ($repls as $from => $to) {
        // Replace both plain and capitalized variants? We'll do a case-sensitive literal replace.
        $body = str_replace($from, $to, $body);
    }

    // Optional: inject a <base> tag so relative URLs resolve (if none exists)
    if (stripos($body, '<base') === false) {
        $base = htmlspecialchars($target, ENT_QUOTES | ENT_HTML5);
        $body = preg_replace('#<head([^>]*)>#i', "<head$1>\n<base href=\"$base\">", $body, 1);
    }

    // Send headers and body
    if ($content_type) header("Content-Type: $content_type");
    http_response_code($http_code ?: 200);
    echo $body;
    exit;
} else {
    // Binary: forward content-type and raw body
    if ($content_type) header("Content-Type: $content_type");
    http_response_code($http_code ?: 200);
    echo $body;
    exit;
}

// ---------------- Helper functions ----------------

function buildForwardHeaders() {
    $out = [];
    // Forward Accept-Language and Cookies if present (but be cautious)
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $out[] = 'Accept-Language: ' . $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    if (!empty($_SERVER['HTTP_COOKIE'])) $out[] = 'Cookie: ' . $_SERVER['HTTP_COOKIE'];
    // We don't forward Host to avoid confusion
    return $out;
}

function parseHeaders($raw) {
    $lines = preg_split("/\r\n|\n|\r/", trim($raw));
    $headers = [];
    foreach ($lines as $i => $line) {
        if ($i === 0) {
            $headers['Status-Line'] = $line;
            continue;
        }
        if (strpos($line, ':') !== false) {
            list($k, $v) = explode(':', $line, 2);
            $headers[trim($k)] = trim($v);
        }
    }
    return $headers;
}

function resolveUrl($url, $base) {
    // nếu đã tuyệt đối thì trả về như cũ
    if (preg_match('#^https?://#i', $url)) return $url;
    // nếu bắt đầu bằng // -> giữ scheme
    if (strpos($url, '//') === 0) {
        $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'http';
        return $scheme . ':' . $url;
    }
    // khác: ghép base
    $p = parse_url($base);
    $scheme = $p['scheme'] ?? 'http';
    $host = $p['host'] ?? '';
    $port = isset($p['port']) ? ':' . $p['port'] : '';
    // nếu url bắt đầu bằng /
    if (strpos($url, '/') === 0) {
        return "$scheme://$host$port" . $url;
    } else {
        // lấy path từ base
        $path = isset($p['path']) ? preg_replace('#/[^/]*$#', '/', $p['path']) : '/';
        return "$scheme://$host$port" . $path . $url;
    }
}

function buildProxyUrl($absoluteUrl, $repl_param = '') {
    // trả về URL gọi chính script này với param u=url
    $self = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME']) . $_SERVER['PHP_SELF'];
    $qs = 'u=' . urlencode($absoluteUrl);
    if ($repl_param !== '') $qs .= '&repl=' . urlencode($repl_param);
    return $self . '?' . $qs;
}

function fixRelativeUrls($html, $base) {
    // Convert src/href attributes that are relative into absolute using base
    $callback = function($m) use ($base) {
        $attr = $m[1];
        $quote = $m[2];
        $url = $m[3];
        $trim = trim($url);
        if (preg_match('#^https?://#i', $trim) || strpos($trim, '//') === 0 || strpos($trim, 'data:') === 0 || strpos($trim, 'javascript:') === 0 || strpos($trim, '#') === 0) {
            return $m[0]; // giữ nguyên
        }
        $abs = resolveUrl($trim, $base);
        return $attr . '=' . $quote . $abs . $quote;
    };
    $html = preg_replace_callback('#(src|href)\s*=\s*(["\'])([^"\']+)\\2#i', $callback, $html);
    return $html;
}

function rewriteLinksToProxy($html, $targetBase) {
    // rewrite any link or src that points to the target host to go through proxy
    $p = parse_url($targetBase);
    $host = $p['host'] ?? '';
    if (!$host) return $html;

    // find urls like http(s)://host/...
    $html = preg_replace_callback('#(href|src)\s*=\s*(["\'])(https?://'.preg_quote($host,'#').'[^"\']*)\\2#i', function($m){
        $attr = $m[1];
        $quote = $m[2];
        $url = $m[3];
        $proxy = buildProxyUrl($url); // uses current repl param from GET if present
        return $attr . '=' . $quote . $proxy . $quote;
    }, $html);

    // Also rewrite plain text urls (e.g., absolute urls inside scripts/styles) -- optional:
    $html = str_ireplace($targetBase, buildProxyUrl($targetBase), $html);

    return $html;
}
