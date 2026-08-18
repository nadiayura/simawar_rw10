<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password Warga - Masukkan Email</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f9fafb;font-family:'Inter',ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji",sans-serif;color:#111827}
        .card{background:#fff;border-radius:12px;border:1px solid #e5e7eb;box-shadow:0 10px 40px rgba(15,23,42,.06);padding:32px 32px 28px;width:100%;max-width:420px}
        .title{font-size:22px;font-weight:700;text-align:center;margin:0 0 24px}
        .brand{font-size:21px;font-weight:700;text-transform:uppercase;color:#111827;text-align:center;margin-bottom:6px}
        .subtitle{font-size:14px;color:#6b7280;text-align:center;margin-bottom:24px}
        .label{font-size:14px;font-weight:600;color:#374151;margin-bottom:6px;display:block;required}
        .input{width:100%;border:1px solid #e5e7eb;border-radius:12px;padding:10px 14px;font-size:14px;color:#111827;outline:none;box-sizing:border-box;background:#f9fafb}
        .input:focus{border-color:#8789b0;box-shadow:0 0 0 1px rgba(135,137,176,.4);background:#fff}
        .btn{width:100%;margin-top:16px;border:none;border-radius:12px;background:#a1a1ff;color:#fff;font-size:14px;font-weight:600;padding:10px 12px;cursor:pointer}
        .btn:hover{background:#8383d1}
        .status{font-size:13px;color:#16a34a;margin-bottom:10px;text-align:center}

        .errors li+li{margin-top:4px}
        .footer{margin-top:16px;font-size:13px;text-align:center;color:#8789b0}
        .footer a{color:#8383d1;text-decoration:none;font-size:14px;font-weight:600}
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">SIMAWAR 10 | WARGA</div>
        <div class="title">Reset password</div>
        <div class="subtitle">Masukkan email terdaftar untuk menerima kode OTP.</div>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <ul class="errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="post" action="{{ route('warga.password.send-otp') }}">
            @csrf
            <label for="email" class="label">Alamat email</label>
            <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" required autofocus>

            <button type="submit" class="btn">Kirim OTP</button>
        </form>

        <div class="footer">
            <a href="{{ url('/warga/login') }}">Kembali ke halaman login</a>
        </div>
    </div>
</body>
</html>
