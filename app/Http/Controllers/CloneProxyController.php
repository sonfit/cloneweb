<?php

namespace App\Http\Controllers;

use App\Models\ClonedSite;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloneProxyController extends Controller
{
    /**
     * Xử lý tất cả requests và proxy/clone từ web_clone
     */
    public function handle(Request $request)
    {
        // Lấy domain hiện tại - bao gồm cả port nếu có
        $host = $request->getHost();
        $port = $request->getPort();
        
        // Nếu port không phải standard (80 cho http, 443 cho https), thêm vào domain
        $currentDomain = $host;
        if ($port && $port != 80 && $port != 443) {
            $currentDomain = $host . ':' . $port;
        }
        
        // Tìm ClonedSite theo domain - thử match với port trước, sau đó không port
        $clonedSite = ClonedSite::where('domain', $currentDomain)->first();
        
        // Nếu không tìm thấy, thử match không port (để tương thích với DB đã lưu không có port)
        if (!$clonedSite) {
            $clonedSite = ClonedSite::where('domain', $host)->first();
        }

        if (!$clonedSite) {
            return response('Domain not configured', 404);
        }

        // Xây dựng target URL từ web_clone + path hiện tại
        $targetBase = rtrim($clonedSite->web_clone, '/');
        $path = $request->path();
        $queryString = $request->getQueryString();

        // Nếu có param 'u' thì dùng nó (cho internal redirects)
        if ($request->has('u')) {
            $target = urldecode($request->get('u'));
        } else {
            // Xây dựng target URL - tránh double slash
            if ($path === '/' || $path === '') {
                $target = $targetBase . '/';
            } else {
                $target = $targetBase . '/' . ltrim($path, '/');
            }
            if ($queryString) {
                $target .= '?' . $queryString;
            }
        }

        // Chuẩn hóa target URL
        if (!preg_match('#^https?://#i', $target)) {
            $target = 'http://' . $target;
        }

        // Lấy replacements từ model hoặc từ request param
        $repls = [];
        if ($request->has('repl')) {
            $repl_param = $request->get('repl');
            $repls = $this->parseReplacements($repl_param);
        } else {
            // Convert từ format Filament [{"find": "...", "replace": "..."}] sang ["from" => "to"]
            $replacements = $clonedSite->string_replace_arr ?? [];
            foreach ($replacements as $item) {
                if (isset($item['find']) && isset($item['replace'])) {
                    $repls[$item['find']] = $item['replace'];
                }
            }
        }

        try {
            // Fetch content từ target
            $response = $this->fetchTarget($target, $request);

            if (!$response) {
                return response('Error fetching target', 502);
            }

            $httpCode = $response['http_code'];
            $contentType = $response['content_type'] ?? null;
            $headers = $response['headers'];
            $body = $response['body'];

            // Xử lý redirect (3xx)
            if ($httpCode >= 300 && $httpCode < 400 && isset($headers['Location'])) {
                $location = $this->resolveUrl($headers['Location'], $target);
                $proxyLocation = $this->buildProxyUrl($location, $repls, $request);
                return redirect($proxyLocation, $httpCode);
            }

            // Kiểm tra content type
            $ct = $contentType ? explode(';', $contentType)[0] : '';
            $isTextLike = preg_match('#^(text/|application/(json|javascript|xml))#i', $ct)
                        || stripos($ct, 'html') !== false;

            if ($isTextLike) {
                // Xử lý HTML/text content
                $body = $this->fixRelativeUrls($body, $target);
                $body = $this->rewriteLinksToProxy($body, $target, $repls, $request);
                $body = $this->rewriteUrlsInContent($body, $target, $repls, $request);
                
                // Áp dụng string replacements
                foreach ($repls as $from => $to) {
                    $body = str_replace($from, $to, $body);
                }

                // Inject <base> tag nếu chưa có
                if (stripos($body, '<base') === false) {
                    $base = htmlspecialchars($target, ENT_QUOTES | ENT_HTML5);
                    $body = preg_replace('#<head([^>]*)>#i', "<head$1>\n<base href=\"$base\">", $body, 1);
                }

                return response($body, $httpCode ?: 200)
                    ->header('Content-Type', $contentType ?? 'text/html; charset=utf-8');
            } else {
                // Binary content - forward trực tiếp
                return response($body, $httpCode ?: 200)
                    ->header('Content-Type', $contentType ?? 'application/octet-stream');
            }
        } catch (\Exception $e) {
            Log::error('Proxy error: ' . $e->getMessage());
            return response('Internal proxy error', 500);
        }
    }

    /**
     * Fetch content từ target URL
     */
    private function fetchTarget(string $target, Request $request): ?array
    {
        try {
            $headers = [];

            // Forward một số headers từ client
            if ($request->hasHeader('Accept-Language')) {
                $headers['Accept-Language'] = $request->header('Accept-Language');
            }
            if ($request->hasHeader('Cookie')) {
                $headers['Cookie'] = $request->header('Cookie');
            }

            $response = Http::withHeaders($headers)
                ->withUserAgent($request->userAgent() ?? 'Laravel Proxy')
                ->withoutRedirecting() // Không tự động follow redirect
                ->withoutVerifying() // Tắt SSL verification (production nên cấu hình đúng)
                ->get($target);

            $httpCode = $response->status();

            // Forward tất cả responses (kể cả 4xx, 5xx) để user thấy đúng response từ server gốc
            $contentType = $response->header('Content-Type');

            // Parse headers - Laravel HTTP trả về headers dạng array
            $responseHeaders = [];
            $allHeaders = $response->headers();
            foreach ($allHeaders as $key => $value) {
                // Laravel trả về header keys dưới dạng lowercase với dấu gạch ngang
                $normalizedKey = ucwords(str_replace('-', ' ', strtolower($key)), '-');
                $responseHeaders[$normalizedKey] = is_array($value) ? $value[0] : $value;
            }
            // Đảm bảo có Location header với key đúng format
            if ($response->hasHeader('location')) {
                $location = $response->header('location');
                $responseHeaders['Location'] = is_array($location) ? $location[0] : $location;
            }

            return [
                'http_code' => $httpCode,
                'content_type' => $contentType,
                'headers' => $responseHeaders,
                'body' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Fetch error: ' . $e->getMessage(), [
                'target' => $target,
                'exception' => $e
            ]);
            return null;
        }
    }

    /**
     * Parse replacement string thành array
     */
    private function parseReplacements(string $replParam): array
    {
        $repls = [];
        foreach (explode(';', $replParam) as $part) {
            $part = trim($part);
            if ($part === '') continue;

            $pair = explode('|', $part, 2);
            if (count($pair) == 2) {
                $from = trim($pair[0]);
                $to = trim($pair[1]);
                if ($from !== '') {
                    $repls[$from] = $to;
                }
            }
        }
        return $repls;
    }

    /**
     * Resolve relative URL thành absolute URL
     */
    private function resolveUrl(string $url, string $base): string
    {
        // Nếu đã tuyệt đối thì trả về như cũ
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        // Nếu bắt đầu bằng // -> giữ scheme
        if (strpos($url, '//') === 0) {
            $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'http';
            return $scheme . ':' . $url;
        }

        // Ghép base
        $p = parse_url($base);
        $scheme = $p['scheme'] ?? 'http';
        $host = $p['host'] ?? '';
        $port = isset($p['port']) ? ':' . $p['port'] : '';

        // Nếu url bắt đầu bằng /
        if (strpos($url, '/') === 0) {
            return "$scheme://$host$port" . $url;
        } else {
            // Lấy path từ base
            $path = isset($p['path']) ? preg_replace('#/[^/]*$#', '/', $p['path']) : '/';
            return "$scheme://$host$port" . $path . $url;
        }
    }

    /**
     * Build proxy URL - giữ nguyên path từ URL gốc, không dùng query params
     */
    private function buildProxyUrl(string $absoluteUrl, array $repls, Request $request): string
    {
        $scheme = $request->getScheme();
        $host = $request->getHost();
        $port = $request->getPort();
        
        // Parse URL gốc để lấy path
        $parsedUrl = parse_url($absoluteUrl);
        $path = $parsedUrl['path'] ?? '/';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';
        
        // Xây dựng base URL với port nếu cần
        $baseUrl = $scheme . '://' . $host;
        if ($port && $port != 80 && $port != 443) {
            $baseUrl .= ':' . $port;
        }
        
        // Chỉ trả về path, không dùng query params u= và repl=
        // Replacements sẽ được lấy từ database khi handle request
        return $baseUrl . $path . $query . $fragment;
    }

    /**
     * Fix relative URLs trong HTML thành absolute URLs
     */
    private function fixRelativeUrls(string $html, string $base): string
    {
        $callback = function($m) use ($base) {
            $attr = $m[1];
            $quote = $m[2];
            $url = $m[3];
            $trim = trim($url);

            // Giữ nguyên nếu đã là absolute, data:, javascript:, hoặc anchor
            if (preg_match('#^https?://#i', $trim)
                || strpos($trim, '//') === 0
                || strpos($trim, 'data:') === 0
                || strpos($trim, 'javascript:') === 0
                || strpos($trim, '#') === 0) {
                return $m[0];
            }

            $abs = $this->resolveUrl($trim, $base);
            return $attr . '=' . $quote . $abs . $quote;
        };

        return preg_replace_callback('#(src|href)\s*=\s*(["\'])([^"\']+)\\2#i', $callback, $html);
    }

    /**
     * Rewrite links trỏ về target host để đi qua proxy
     */
    private function rewriteLinksToProxy(string $html, string $targetBase, array $repls, Request $request): string
    {
        $p = parse_url($targetBase);
        $host = $p['host'] ?? '';

        if (!$host) {
            return $html;
        }

        // Rewrite các attributes HTML phổ biến
        $attributes = ['href', 'src', 'action', 'data-src', 'data-url', 'data-href', 'cite', 'formaction'];
        $escapedHost = preg_quote($host, '#');
        
        foreach ($attributes as $attr) {
            // Pattern cho attribute="url" hoặc attribute='url'
            $html = preg_replace_callback(
                '#' . preg_quote($attr, '#') . '\s*=\s*(["\'])(https?://' . $escapedHost . '[^"\']*)\\1#i',
                function($m) use ($attr, $repls, $request) {
                    $quote = $m[1];
                    $url = $m[2];
                    $proxy = $this->buildProxyUrl($url, $repls, $request);
                    return $attr . '=' . $quote . $proxy . $quote;
                },
                $html
            );
        }

        // Rewrite URLs trong CSS (url(...), background-image, etc.)
        $html = preg_replace_callback(
            '#(url|background-image|background)\s*\([\s]*(["\']?)(https?://' . $escapedHost . '[^"\'\s)]+)\\2[\s]*\)#i',
            function($m) use ($repls, $request) {
                $property = $m[1];
                $quote = $m[2];
                $url = $m[3];
                $proxy = $this->buildProxyUrl($url, $repls, $request);
                return $property . '(' . $quote . $proxy . $quote . ')';
            },
            $html
        );

        // Rewrite URLs trong inline styles
        $html = preg_replace_callback(
            '#style\s*=\s*["\']([^"\']*url\([^)]*https?://' . $escapedHost . '[^)]+\)[^"\']*)["\']#i',
            function($m) use ($repls, $request, $host) {
                $style = $m[1];
                $style = preg_replace_callback(
                    '#url\s*\([\s]*(["\']?)(https?://' . preg_quote($host, '#') . '[^"\'\s)]+)\\1[\s]*\)#i',
                    function($urlMatch) use ($repls, $request) {
                        $url = $urlMatch[2];
                        $proxy = $this->buildProxyUrl($url, $repls, $request);
                        return 'url(' . $urlMatch[1] . $proxy . $urlMatch[1] . ')';
                    },
                    $style
                );
                return 'style="' . $style . '"';
            },
            $html
        );

        return $html;
    }

    /**
     * Rewrite URLs trong text content, JavaScript và các nơi khác chưa được xử lý
     */
    private function rewriteUrlsInContent(string $html, string $targetBase, array $repls, Request $request): string
    {
        $p = parse_url($targetBase);
        $host = $p['host'] ?? '';

        if (!$host) {
            return $html;
        }

        $escapedHost = preg_quote($host, '#');
        
        // Rewrite URLs trong JavaScript strings (trong thẻ <script>)
        $html = preg_replace_callback(
            '#(<script[^>]*>)(.*?)(</script>)#is',
            function($m) use ($repls, $request, $escapedHost) {
                $scriptTag = $m[1];
                $scriptContent = $m[2];
                $scriptClose = $m[3];
                
                // Tìm và thay thế URLs trong JavaScript - tránh các string literals đã có quotes
                $that = $this;
                $requestHost = $request->getHost();
                $scriptContent = preg_replace_callback(
                    '#(https?://' . $escapedHost . '[^\s\'"<>\)]+)#i',
                    function($urlMatch) use ($repls, $request, $that, $requestHost) {
                        $url = $urlMatch[1];
                        // Bỏ qua nếu đã là proxy URL (chứa domain của request)
                        if (strpos($url, $requestHost) !== false) {
                            return $url;
                        }
                        return $that->buildProxyUrl($url, $repls, $request);
                    },
                    $scriptContent
                );
                
                return $scriptTag . $scriptContent . $scriptClose;
            },
            $html
        );

        // Rewrite URLs trong text content (ngoài HTML tags và attributes đã xử lý)
        // Sử dụng cách đơn giản: replace tất cả URLs còn lại trỏ về target host
        // Nhưng tránh replace những URL đã chứa domain của request (đã được xử lý)
        $that = $this;
        $requestHost = $request->getHost();
        $html = preg_replace_callback(
            '#(https?://' . $escapedHost . '[^\s<>"\'\)]+)#i',
            function($m) use ($repls, $request, $host, $that, $requestHost) {
                $url = $m[1];
                
                // Bỏ qua nếu URL đã chứa domain của request (đã được xử lý thành proxy URL)
                if (strpos($url, $requestHost) !== false) {
                    return $url;
                }
                
                return $that->buildProxyUrl($url, $repls, $request);
            },
            $html
        );

        return $html;
    }
}
