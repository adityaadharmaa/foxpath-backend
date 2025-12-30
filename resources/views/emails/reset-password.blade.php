<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body>
    <h2>Hello {{ $user->username }}</h2>

    <p>Kami menerima permintaan reset password akun Foxpath kamu.</p>

    <p>
        <a href="{{ $resetUrl }}"
           style="padding:12px 20px;background:#2563eb;color:#fff;text-decoration:none;">
            Reset Password
        </a>
    </p>

    <p>
        Link ini hanya bisa digunakan <strong>satu kali</strong> dan akan kedaluwarsa.
    </p>

    <p>
        Jika kamu tidak merasa meminta reset password, abaikan email ini.
    </p>

    <hr>
    <small>Foxpath Team</small>
</body>
</html>
