<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi OTP Warga</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f9fafb;font-family:'Inter',ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji",sans-serif;color:#111827}
        .card{background:#fff;border-radius:12px;border:1px solid #e5e7eb;box-shadow:0 10px 40px rgba(15,23,42,.06);padding:32px 32px 28px;width:100%;max-width:420px}
        .title{font-size:20px;font-weight:700;text-align:center;margin:0 0 24px}
        .brand{font-size:21px;font-weight:700;;text-transform:uppercase;color:#111827;text-align:center;margin-bottom:6px}
        .subtitle{font-size:13px;color:#6b7280;text-align:center;margin-bottom:24px}
        .btn{width:100%;margin-top:16px;border:none;border-radius:12px;background:#a1a1ff;color:#fff;font-size:14px;font-weight:600;padding:10px 12px;cursor:pointer}
        .btn:hover{background:#8383d1}
        .status{font-size:13px;color:#16a34a;margin-bottom:10px;text-align:center}
        .errors{margin:0 0 10px;padding:8px 10px;border-radius:8px;background:#fef2f2;color:#b91c1c;font-size:13px;list-style:none}
        .errors li+li{margin-top:4px}
        .footer{margin-top:16px;font-size:13px;text-align:center;color:#8789b0}
        .footer a{color:#8383d1;text-decoration:none;font-size:14px;font-weight:600}
        .otp-inputs{display:flex;justify-content:center;gap:10px;margin-bottom:8px}
        .otp-box{width:52px;height:52px;border-radius:12px;border:1px solid #e5e7eb;background:#f3f4f6;text-align:center;font-size:22px;font-weight:600;outline:none}
        .otp-box:focus{border-color:#8789b0;box-shadow:0 0 0 1px rgba(135,137,176,.4);background:#fff}
        .otp-helper{font-size:12px;color:#6b7280;text-align:center;margin-bottom:4px}
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">SIMAWAR 10 | WARGA</div>
        <h1 class="title">Verifikasi OTP</h1>
        <div class="subtitle">Masukkan 4 digit kode OTP yang dikirim ke WhatsApp Anda.</div>

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

        <form method="post" action="{{ route('warga.password.check-otp') }}" id="otp-form">
            @csrf

            <div class="otp-helper">Kode OTP</div>
            <div class="otp-inputs">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-box" id="otp_1" autocomplete="one-time-code">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-box" id="otp_2">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-box" id="otp_3">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-box" id="otp_4">
            </div>
            <input type="hidden" name="otp" id="otp_combined">

            <button type="submit" class="btn">Verifikasi</button>
        </form>

        <div class="footer">
            Tidak menerima kode?
            <a href="{{ url('/reset-password/warga') }}">Kembali ke halaman email</a>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var inputs = [
                document.getElementById('otp_1'),
                document.getElementById('otp_2'),
                document.getElementById('otp_3'),
                document.getElementById('otp_4')
            ];
            inputs[0].focus();
            inputs.forEach(function (input, index) {
                input.addEventListener('input', function () {
                    var value = input.value.replace(/\D/g, '');
                    input.value = value.slice(0, 1);
                    if (input.value && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                });
                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Backspace' && !input.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });
            });
            var form = document.getElementById('otp-form');
            form.addEventListener('submit', function (event) {
                var code = inputs.map(function (input) { return input.value; }).join('');
                document.getElementById('otp_combined').value = code;
            });
        });
    </script>
</body>
</html>
