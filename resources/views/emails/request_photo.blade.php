<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; background-color: #f4f4f7; padding: 20px;">
    <div style="max-w: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        
        <!-- Header -->
        <div style="background-color: #5949be; padding: 30px; text-align: center;">
            <h2 style="color: #ffffff; margin: 0; font-size: 20px;">CẬP NHẬT THÔNG TIN ĐẠI BIỂU</h2>
        </div>

        <!-- Body -->
        <div style="padding: 30px;">
            <p>Kính gửi quý đại biểu <strong>{{ $guest->full_name }}</strong>,</p>
            
            <p>Để chuẩn bị cho công tác đón tiếp và điểm danh tự động bằng <strong>Công nghệ nhận diện khuôn mặt AI</strong> tại sự kiện <strong>"{{ $meeting->title }}"</strong>, Ban tổ chức trân trọng kính mời quý đại biểu cung cấp một ảnh chân dung ghi nhận hệ thống.</p>
            
            <p style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; color: #b45309; font-size: 14px; border-radius: 4px;">
                <strong>💡 Lưu ý chụp ảnh:</strong> Bạn có thể dùng điện thoại tự chụp selfie trực tiếp, chọn nơi đủ ánh sáng, không đeo khẩu trang hoặc kính râm để AI đạt độ chính xác cao nhất.
            </p>

            <!-- Nút bấm hành động -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $secureUrl }}" 
                   style="background-color: #5949be; color: #ffffff; padding: 14px 28px; font-weight: bold; text-decoration: none; border-radius: 8px; display: inline-block; box-shadow: 0 4px 12px rgba(89,73,190,0.3);">
                   📸 Bật Camera / Tải Ảnh Lên Ngay
                </a>
            </div>

            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">
            
            <!-- Thông tin sự kiện -->
            <p style="font-size: 14px; color: #666666; margin: 5px 0;"><strong>📍 Địa điểm:</strong> {{ $meeting->location }}</p>
            <p style="font-size: 14px; color: #666666; margin: 5px 0;"><strong>⏰ Thời gian:</strong> {{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i d/m/Y') }}</p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #9ca3af;">
            Đây là email tự động gửi từ hệ thống AI Attendance. Vui lòng không phản hồi email này.
        </div>
    </div>
</body>
</html>