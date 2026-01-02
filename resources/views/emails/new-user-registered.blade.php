<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>User Baru Terdaftar</title>
    <style type="text/css">
        /* Base Reset */
        body { margin: 0; padding: 0; min-width: 100%; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.5; background-color: #f4f5f6; color: #333333; }
        
        /* Layout */
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f5f6; padding-bottom: 40px; }
        .content { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        
        /* Header - Warna Indigo/Ungu untuk Admin Notification */
        .header { background-color: #4f46e5; padding: 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 20px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        
        /* Body */
        .body { padding: 30px; }
        
        /* User Detail Box */
        .user-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; margin: 20px 0; }
        .user-card table { width: 100%; }
        .user-card td { padding: 5px 0; vertical-align: top; }
        .label { color: #64748b; font-size: 14px; width: 30%; font-weight: bold; }
        .value { color: #334155; font-size: 14px; width: 70%; }
        
        /* Button */
        .btn-container { text-align: center; margin-top: 30px; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #4f46e5; color: #ffffff !important; border-radius: 6px; font-weight: bold; text-decoration: none; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn:hover { background-color: #4338ca; }
        
        /* Footer */
        .footer { text-align: center; padding: 20px; color: #999999; font-size: 12px; }
        
        @media only screen and (max-width: 600px) {
            .body { padding: 20px; }
            .content { width: 100% !important; border-radius: 0; }
            .label { display: block; width: 100%; margin-bottom: 2px; }
            .value { display: block; width: 100%; margin-bottom: 10px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <br>
        <div class="content">
            <div class="header">
                <h1>FOXPATH ADMIN</h1>
            </div>

            <div class="body">
                <h3>Halo, Admin! 👋</h3>
                
                <p>Ada pengguna baru yang baru saja mendaftar di aplikasi Foxpath.</p>
                <p>Berikut adalah detail informasi pengguna tersebut:</p>

                <div class="user-card">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="label">Username:</td>
                            <td class="value">{{ $newUser->username }}</td>
                        </tr>
                        <tr>
                            <td class="label">Email:</td>
                            <td class="value">
                                <a href="mailto:{{ $newUser->email }}" style="color: #4f46e5;">{{ $newUser->email }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Waktu Daftar:</td>
                            <td class="value">{{ $newUser->created_at->timezone('Asia/Makassar')->format('d M Y, H:i') }} WIB</td>
                        </tr>
                        <tr>
                            <td class="label">Status:</td>
                            <td class="value">
                                <span style="background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:99px; font-size:12px;">Email Unverified</span>
                            </td>
                        </tr>
                    </table>
                </div>

                <p>Silakan login ke dashboard admin untuk meninjau atau memvalidasi pengguna ini.</p>

                <div class="btn-container">
                    <a href="{{ config('app.frontend_url') }}/admin/users" class="btn">Lihat User di Dashboard</a>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Foxpath System Notification.</p>
            <p>Email ini dikirim otomatis oleh sistem untuk Administrator.</p>
        </div>
    </div>
</body>
</html>