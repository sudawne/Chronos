<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f6fd; margin: 0; padding: 20px; }
        .container { max-width: 600px; background: white; margin: 0 auto; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background: #5949be; color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; text-align: center; color: #333; }
        .qr-box { margin: 20px auto; padding: 20px; border: 2px dashed #5949be; display: inline-block; border-radius: 16px; background: #fafaff; }
        .info-table { width: 100%; margin-top: 20px; border-collapse: collapse; text-align: left; }
        .info-table th { color: #888; padding: 10px; border-bottom: 1px solid #eee; width: 40%; }
        .info-table td { color: #333; padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; }
        .footer { background: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>THƯ MỜI ĐIỂM DANH</h1>
        </div>
        <div class="content">
            <p>Xin chào <strong>{{ $guest->full_name }}</strong>,</p>
            <p>Bạn đã được đăng ký tham gia sự kiện <strong>{{ $meeting->title }}</strong>.</p>
            
            <div class="qr-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrData) }}" alt="Mã QR Check-in">
                
                <p style="margin-top: 10px; font-size: 12px; color: #5949be; font-weight: bold;">ĐƯA MÃ NÀY VÀO CAMERA ĐỂ CHECK-IN</p>
            </div>

            <table class="info-table">
                <tr><th>Thời gian:</th><td>{{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i - d/m/Y') }}</td></tr>
                <tr><th>Địa điểm:</th><td>{{ $meeting->location }}</td></tr>
                <tr><th>Vị trí ghế:</th><td>{{ $guest->seat_location ?? 'Tự do' }}</td></tr>
            </table>

            <p style="margin-top: 30px; font-size: 14px; line-height: 1.5;">Hệ thống có hỗ trợ điểm danh nhận diện khuôn mặt (FaceID). Mã QR này là phương thức dự phòng trong trường hợp bạn đeo khẩu trang hoặc hệ thống AI gặp sự cố.</p>
        </div>
        <div class="footer">
            &copy; 2026 Hệ thống CHRONOS AI. Được phát triển bởi Sinh viên Đại học Kiên Giang.
        </div>
    </div>
</body>
</html>