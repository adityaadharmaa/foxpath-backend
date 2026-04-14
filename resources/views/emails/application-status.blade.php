<!DOCTYPE html>
<html>
<head>
    <title>Update Status Lamaran FoxPath</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo h1 { color: #333; font-size: 24px; font-weight: bold; letter-spacing: 1px; }
        .content { color: #555; line-height: 1.6; }
        .status-badge { 
            text-align: center; 
            padding: 10px; 
            border-radius: 8px; 
            font-weight: bold; 
            margin: 20px 0;
            text-transform: uppercase;
        }
        .accepted { background-color: #dcfce7; color: #16a34a; border: 1px solid #bcf0da; }
        .rejected { background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
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
            <p>Halo <strong>{{ $application->user->profile->full_name ?? $application->user->username }}</strong>,</p>
            <p>Terima kasih telah berpartisipasi dalam proses seleksi magang untuk program <strong>{{ $application->program->name }}</strong>.</p>
            
            <p>Berdasarkan hasil perhitungan kriteria dan kuota program, lamaran Anda dinyatakan:</p>

            <div class="status-badge {{ $application->status === 'accepted' ? 'accepted' : 'rejected' }}">
                {{ $application->status === 'accepted' ? 'DITERIMA (LULUS)' : 'TIDAK DITERIMA (TIDAK LULUS)' }}
            </div>

            @if($application->status === 'accepted')
                <p>Selamat! Anda telah terpilih untuk bergabung. Tahap selanjutnya adalah memulai penempatan pada tanggal <strong>{{ \Carbon\Carbon::parse($application->placement_start_at)->format('d M Y') }}</strong>.</p>
                <div class="btn-container">
                    <a href="{{ config('app.frontend_url') }}/user/dashboard" class="btn">Lihat Detail Penempatan</a>
                </div>
            @else
                <p>Kami memohon maaf karena saat ini belum bisa menerima Anda di program ini. Tetap semangat, karena data Anda akan tetap menjadi pertimbangan kami untuk program di masa mendatang.</p>
            @endif

            <p>Jika ada pertanyaan, silakan hubungi tim administrasi kami melalui dashboard.</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} FoxPath Team. All rights reserved.
        </div>
    </div>
</body>
</html>