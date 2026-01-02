<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Password Berhasil Diubah</title>
    <style type="text/css">
        body { margin: 0; padding: 0; min-width: 100%; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; background-color: #f4f5f6; color: #333333; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f5f6; padding-bottom: 40px; }
        .content { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #10b981; /* Warna Hijau (Success) agar beda dgn Reset */ padding: 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: bold; letter-spacing: 1px; }
        .body { padding: 30px; }
        .alert-box { background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 6px; color: #166534; margin: 20px 0; font-size: 14px; }
        .footer { text-align: center; padding: 20px; color: #999999; font-size: 12px; }
        .divider { border-top: 1px solid #e5e7eb; margin: 30px 0; }
        
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
                
                <p>Notifikasi ini dikirim untuk memberitahu bahwa kata sandi akun <strong>Foxpath</strong> Anda baru saja berhasil diubah.</p>

                <div class="alert-box">
                    <strong>Informasi Perubahan:</strong><br>
                    Waktu: {{ now()->format('d M Y, H:i') }}<br>
                    Status: Berhasil
                </div>
                
                <p>Jika Anda yang melakukan perubahan ini, silakan abaikan email ini. Anda dapat login kembali menggunakan kata sandi baru Anda.</p>

                <div class="divider"></div>

                <p style="color: #dc2626; font-weight: bold;">
                    ⚠️ Bukan Anda yang mengubahnya?
                </p>
                <p>
                    Jika Anda tidak merasa mengubah kata sandi, akun Anda mungkin telah diakses oleh orang lain. Mohon segera hubungi tim support kami atau lakukan reset password ulang.
                </p>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Foxpath Security Team.</p>
        </div>
    </div>
</body>
</html>