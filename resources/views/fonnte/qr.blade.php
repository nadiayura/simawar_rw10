<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code</title>
    <style>
        body { background:#f3f4f6; font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
        .card { background:#fff; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,.08); width: 420px; max-width: 92vw; }
        .card-header { padding:16px 20px; border-bottom:1px solid #e5e7eb; font-weight:700; color:#111827; display:flex; align-items:center; gap:8px; }
        .card-body { padding:20px; }
        .hint { font-size:12px; color:#6b7280; margin-top:12px; text-align:center; }
        iframe, img { width:100%; height:380px; border:0; border-radius:12px; background:#fafafa; }
        .error { color:#b91c1c; text-align:center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">QR Code</div>
        <div class="card-body">
            @if ($error)
                <div class="error">{{ $error }}</div>
            @elseif(!empty($png))
                <img src="data:image/png;base64,{{ $png }}" alt="QR Code">
                <div class="hint">Scan QR menggunakan aplikasi WhatsApp yang terhubung. Fonnte tidak bertanggung jawab atas risiko pemblokiran.</div>
            @endif
        </div>
    </div>
</body>
</html>
