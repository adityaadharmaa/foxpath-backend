<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Reset Password Foxpath</title>
    <style type="text/css">
        /* Reset styles untuk kompatibilitas email client */
        body { margin: 0; padding: 0; min-width: 100%; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; background-color: #f4f5f6; color: #333333; }
        a { color: #2563eb; text-decoration: none; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f5f6; padding-bottom: 40px; }
        .content { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #2563eb; padding: 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: bold; letter-spacing: 1px; }
        .body { padding: 30px; }
        .button-container { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; border-radius: 6px; font-weight: bold; text-decoration: none; font-size: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn:hover { background-color: #1d4ed8; }
        .footer { text-align: center; padding: 20px; color: #999999; font-size: 12px; }
        .divider { border-top: 1px solid #e5e7eb; margin: 30px 0; }
        .fallback-link { font-size: 12px; color: #6b7280; word-break: break-all; }
        
        /* Mobile responsive */
        @media only screen and (max-width: 600px) {
            .body { padding: 20px; }
            .content { width: 100% !important; border-radius: 0; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <br>
        <div class="content">
            <div class="header">
                <h1>FOXPATH</h1>
            </div>

            <div class="body">
                <h3>Halo, {{ $user->username }}! 👋</h3>
                
                <p>Kami menerima permintaan untuk mereset kata sandi akun <strong>Foxpath</strong> Anda. Jangan khawatir, hal ini biasa terjadi jika Anda lupa kata sandi.</p>

                <p>Untuk membuat kata sandi baru, silakan klik tombol di bawah ini:</p>

                <div class="button-container">
                    <a href="{{ $resetUrl }}" class="btn">Reset Password Saya</a>
                </div>

                <p>Demi keamanan akun Anda, link ini akan kedaluwarsa dalam <strong>60 menit</strong>.</p>
                
                <p>Jika Anda tidak merasa melakukan permintaan ini, silakan abaikan email ini. Akun Anda tetap aman dan tidak ada perubahan yang dilakukan.</p>

                <div class="divider"></div>

                <p style="font-size: 14px; color: #666;">
                    Jika tombol di atas tidak berfungsi, salin dan tempel link berikut ke browser Anda:
                </p>
                <p class="fallback-link">
                    <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
                </p>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Foxpath Development Team.</p>
            <p>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>