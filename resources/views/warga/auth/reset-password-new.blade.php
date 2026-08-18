<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Baru Warga</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f9fafb;font-family:'Inter',ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji",sans-serif;color:#111827}
        .card{background:#fff;border-radius:12px;border:1px solid #e5e7eb;box-shadow:0 10px 40px rgba(15,23,42,.06);padding:32px 32px 28px;width:100%;max-width:420px}
        .title{font-size:20px;font-weight:700;text-align:center;margin:0 0 24px}
        .brand{font-size:21px;font-weight:700;color:#111827;text-align:center;margin-bottom:6px}
        .subtitle{font-size:14px;color:#6b7280;text-align:center;margin-bottom:24px}
        .label{font-size:12px;color:#374151;margin-bottom:6px;display:block}
        .input-group{position:relative}
        .input{width:100%;border:1px solid #e5e7eb;border-radius:12px;padding:10px 40px 10px 14px;font-size:14px;color:#111827;outline:none;box-sizing:border-box;background:#f9fafb}
        .input:focus{border-color:#8789b0;box-shadow:0 0 0 1px rgba(135,137,176,.4);background:#fff}
        .toggle-password{position:absolute;top:50%;right:12px;transform:translateY(-50%);border:none;background:transparent;padding:0;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;color:#9ca3af}
        .toggle-password:focus{outline:2px solid transparent}
        .toggle-password svg{width:18px;height:18px;display:block}
        .toggle-password .icon-eye-off{display:none}
        .toggle-password.is-visible{color:#4b5563}
        .toggle-password.is-visible .icon-eye{display:none}
        .toggle-password.is-visible .icon-eye-off{display:block}
        .btn{width:100%;margin-top:16px;border:none;border-radius:12px;background:#a1a1ff;color:#fff;font-size:14px;font-weight:600;padding:10px 12px;cursor:pointer}
        .btn:hover{background:#8383d1}
        .errors{margin:0 0 10px;padding:8px 10px;border-radius:8px;background:#fef2f2;color:#b91c1c;font-size:13px;list-style:none}
        .errors li+li{margin-top:4px}
        .footer{margin-top:16px;font-size:13px;text-align:center;color:#8789b0}
        .footer a{color:#8383d1;text-decoration:none;font-size:14px;font-weight:600}
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">SIMAWAR 10 | WARGA</div>
        <h1 class="title">Password baru</h1>
        <div class="subtitle">Silakan atur password baru untuk akun Anda.</div>

        @if ($errors->any())
            <ul class="errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="post" action="{{ route('warga.password.update') }}">
            @csrf
            <label for="password" class="label">Password baru</label>
            <div class="input-group">
                <input id="password" class="input" type="password" name="password" required autofocus>
                <button type="button" class="toggle-password" data-target="password" aria-label="Tampilkan password">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g class="icon-eye">
                            <path d="M12 5C7 5 3.1 8 1.5 12C3.1 16 7 19 12 19C17 19 20.9 16 22.5 12C20.9 8 17 5 12 5Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 15.5C13.933 15.5 15.5 13.933 15.5 12C15.5 10.067 13.933 8.5 12 8.5C10.067 8.5 8.5 10.067 8.5 12C8.5 13.933 10.067 15.5 12 15.5Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </g>
                        <g class="icon-eye-off">
                            <path d="M4.5 4.5L19.5 19.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9.88 9.88C9.34 10.42 9 11.17 9 12C9 13.66 10.34 15 12 15C12.83 15 13.58 14.66 14.12 14.12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M6.76 6.76C5.14 7.79 3.79 9.25 2.9 11.03C2.7 11.44 2.7 11.96 2.9 12.37C4.5 15.6 8.02 18 12 18C13.35 18 14.64 17.73 15.82 17.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M11.03 6C9.33 6.11 7.74 6.67 6.38 7.56" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M17.24 7.76C18.86 8.79 20.21 10.25 21.1 12.03C21.3 12.44 21.3 12.96 21.1 13.37C20.44 14.68 19.52 15.82 18.41 16.72" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </g>
                    </svg>
                </button>
            </div>

            <label for="password_confirmation" class="label" style="margin-top:12px">Konfirmasi password</label>
            <div class="input-group">
                <input id="password_confirmation" class="input" type="password" name="password_confirmation" required>
                <button type="button" class="toggle-password" data-target="password_confirmation" aria-label="Tampilkan password">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g class="icon-eye">
                            <path d="M12 5C7 5 3.1 8 1.5 12C3.1 16 7 19 12 19C17 19 20.9 16 22.5 12C20.9 8 17 5 12 5Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 15.5C13.933 15.5 15.5 13.933 15.5 12C15.5 10.067 13.933 8.5 12 8.5C10.067 8.5 8.5 10.067 8.5 12C8.5 13.933 10.067 15.5 12 15.5Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </g>
                        <g class="icon-eye-off">
                            <path d="M4.5 4.5L19.5 19.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9.88 9.88C9.34 10.42 9 11.17 9 12C9 13.66 10.34 15 12 15C12.83 15 13.58 14.66 14.12 14.12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M6.76 6.76C5.14 7.79 3.79 9.25 2.9 11.03C2.7 11.44 2.7 11.96 2.9 12.37C4.5 15.6 8.02 18 12 18C13.35 18 14.64 17.73 15.82 17.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M11.03 6C9.33 6.11 7.74 6.67 6.38 7.56" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M17.24 7.76C18.86 8.79 20.21 10.25 21.1 12.03C21.3 12.44 21.3 12.96 21.1 13.37C20.44 14.68 19.52 15.82 18.41 16.72" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </g>
                    </svg>
                </button>
            </div>

            <button type="submit" class="btn">Ubah</button>
        </form>

        <div class="footer">
            <a href="{{ url('/warga/login') }}">Kembali ke halaman login</a>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded',function(){var t=document.querySelectorAll('.toggle-password');t.forEach(function(t){t.addEventListener('click',function(){var e=this.getAttribute('data-target'),a=document.getElementById(e);if(a){var r='password'===a.type;a.type=r?'text':'password';this.setAttribute('aria-label',r?'Sembunyikan password':'Tampilkan password');this.classList.toggle('is-visible',r)}})})});
    </script>
</body>
</html>
