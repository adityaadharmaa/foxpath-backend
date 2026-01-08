<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Email FoxPath</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo h1 { color: #333; font-size: 24px; font-weight: bold; letter-spacing: 1px; }
        .content { color: #555; line-height: 1.6; }
        .btn-container { text-align: center; margin: 30px 0; }
        .btn { background-color: #2563EB; color: #ffffff !important; padding: 12px 24px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block; }
        .footer { margin-top: 30px; font-size: 12px; color: #aaa; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>FOXPATH</h1>
        </div>
        
        <div class="content">
            <p>Halo <strong>{{ $username }}</strong>,</p>
            <p>Terima kasih telah mendaftar di FoxPath Sistem Magang SAW.</p>
            <p>Untuk mulai menggunakan akun Anda dan melamar magang, mohon verifikasi alamat email Anda dengan mengklik tombol di bawah ini:</p>
            
            <div class="btn-container">
                <a href="{{ $url }}" class="btn">Verifikasi Email Saya</a>
            </div>
            
            <p>Tautan ini akan kadaluarsa dalam 60 menit.</p>
            <p>Jika Anda tidak merasa mendaftar, abaikan email ini.</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} FoxPath Team. All rights reserved.
        </div>
    </div>
</body>
</html>