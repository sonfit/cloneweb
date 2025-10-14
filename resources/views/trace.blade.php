<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 16px; max-width: 720px; }
        .loading { color: #555; }
        pre { background: #f6f8fa; padding: 12px; border-radius: 6px; overflow: auto; }
    </style>
    <script>
        async function run() {
            @if(isset($error))
                document.getElementById('msg').textContent = '{{ $error }}';
                return;
            @endif

            const params = new URLSearchParams({
                sdt: '{{ $query['sdt'] ?? '' }}',
                cccd: '{{ $query['cccd'] ?? '' }}',
                fb: '{{ $query['fb'] ?? '' }}'
            });
            const apiUrl = `/tra-cuu/api?${params.toString()}`;
            const msg = document.getElementById('msg');
            const out = document.getElementById('out');
            msg.textContent = 'Đang xử lý dữ liệu, vui lòng chờ...';
            try {
                const res = await fetch(apiUrl, { method: 'GET', cache: 'no-store', headers: { 'Accept': 'application/json' } });
                const data = await res.json();

                if (data && String(data.status).toLowerCase() === 'processing' && data.job_id) {
                    msg.textContent = 'Đang xử lý dữ liệu, vui lòng chờ...';
                    const jobId = data.job_id;
                    // Poll tới khi DONE/FAILED (một vòng poll đơn giản)
                    while (true) {
                        await new Promise(r => setTimeout(r, 3000));
                        const r2 = await fetch(`/tra-cuu/api/${jobId}?_t=${Date.now()}`, { method: 'GET', cache: 'no-store', headers: { 'Accept': 'application/json' } });
                        const d2 = await r2.json();
                        const st = d2 && d2.status ? String(d2.status).toLowerCase() : '';
                        if (st === 'done' || st === 'failed') {
                            msg.textContent = st === 'done' ? 'Hoàn tất' : 'Thất bại';
                            if (d2 && d2.message) {
                                out.textContent = d2.message;
                            } else if (d2 && d2.result != null) {
                                out.textContent = JSON.stringify(d2.result, null, 2);
                            } else {
                                out.textContent = 'Không có dữ liệu';
                            }
                            break;
                        }
                    }
                } else {
                    const st = data && data.status ? String(data.status).toLowerCase() : '';
                    msg.textContent = st === 'done' ? 'Hoàn tất' : (data.message || '');
                    if (data && data.message && st !== 'done') {
                        out.textContent = data.message;
                    } else if (data && data.result != null) {
                        out.textContent = JSON.stringify(data.result, null, 2);
                    } else {
                        out.textContent = 'Không có dữ liệu';
                    }
                }
            } catch (e) {
                msg.textContent = 'Có lỗi xảy ra';
                out.textContent = String(e);
            }
        }
        document.addEventListener('DOMContentLoaded', run);
    </script>
    </head>
<body>
<div class="card">
    <h3>Tra cứu</h3>
    <div id="msg" class="loading">Chuẩn bị...</div>
    <pre id="out"></pre>
    </div>
</body>
</html>

