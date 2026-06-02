<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web AI Check-in | {{ $meeting->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f172a; }
        /* Lật ngược video để giống soi gương */
        #videoElement { transform: scaleX(-1); }
        #overlayCanvas { transform: scaleX(-1); pointer-events: none; }
    </style>
</head>
<body class="h-screen flex flex-col items-center justify-center relative bg-slate-900 text-white">

    <div class="mb-6 text-center">
        <h1 class="text-3xl font-bold text-indigo-400">Web AI Attendance</h1>
        <p class="text-slate-400">{{ $meeting->title }}</p>
    </div>

    <div class="relative rounded-2xl overflow-hidden border-4 border-slate-700 shadow-[0_0_40px_rgba(79,70,229,0.2)] bg-black" style="width: 800px; height: 600px;">
        <video id="videoElement" autoplay playsinline class="absolute top-0 left-0 w-full h-full object-cover"></video>
        
        <canvas id="overlayCanvas" class="absolute top-0 left-0 w-full h-full"></canvas>
        
        <canvas id="captureCanvas" class="hidden"></canvas>
    </div>

    <div class="mt-6 flex gap-4">
        <a href="{{ route('meetings.show', $meeting->id) }}" class="px-6 py-2 bg-slate-700 hover:bg-slate-600 rounded-full font-semibold transition-colors">
            Quay lại quản lý
        </a>
    </div>

    <script>
        const meetingId = {{ $meeting->id }};
        const video = document.getElementById('videoElement');
        const overlayCanvas = document.getElementById('overlayCanvas');
        const overlayCtx = overlayCanvas.getContext('2d');
        const captureCanvas = document.getElementById('captureCanvas');
        const captureCtx = captureCanvas.getContext('2d');

        let isProcessing = false;

        // 1. XIN QUYỀN VÀ MỞ WEBCAM NGƯỜI DÙNG
        navigator.mediaDevices.getUserMedia({ video: { width: 800, height: 600 } })
            .then(stream => { 
                video.srcObject = stream; 
                // Khi video chạy, set kích thước chuẩn cho canvas vẽ
                video.onloadedmetadata = () => {
                    overlayCanvas.width = video.videoWidth;
                    overlayCanvas.height = video.videoHeight;
                    captureCanvas.width = video.videoWidth;
                    captureCanvas.height = video.videoHeight;
                };
            })
            .catch(err => alert("Lỗi: Không thể mở Camera trên trình duyệt. Hãy kiểm tra quyền!"));

        // 2. VÒNG LẶP CHỤP ẢNH -> GỬI API -> VẼ KHUNG HÌNH (Gửi 1.5 giây / 1 lần)
        setInterval(() => {
            if (video.videoWidth === 0 || isProcessing) return;
            
            isProcessing = true; // Khóa lại không gửi dồn dập

            // Chụp khung hình hiện tại
            captureCtx.drawImage(video, 0, 0, captureCanvas.width, captureCanvas.height);
            const imageBase64 = captureCanvas.toDataURL('image/jpeg', 0.7);

            // Gửi sang Python API
            let formData = new FormData();
            formData.append('image_base64', imageBase64);
            formData.append('meeting_id', meetingId);

            fetch('http://localhost:8001/nhan_dien', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                // Xóa nét vẽ cũ
                overlayCtx.clearRect(0, 0, overlayCanvas.width, overlayCanvas.height);

                if (data.status === 'success' && data.detections) {
                    data.detections.forEach(det => {
                        let [x1, y1, x2, y2] = det.box;
                        let color = det.color;
                        let name = det.name;

                        // Vẽ khung chữ nhật
                        overlayCtx.beginPath();
                        overlayCtx.lineWidth = "4";
                        overlayCtx.strokeStyle = color;
                        overlayCtx.rect(x1, y1, x2 - x1, y2 - y1);
                        overlayCtx.stroke();

                        // Vẽ nền đen cho chữ dễ đọc
                        overlayCtx.fillStyle = "rgba(0, 0, 0, 0.6)";
                        overlayCtx.fillRect(x1, y1 - 40, (x2 - x1), 40);

                        // Vẽ Tên người
                        overlayCtx.fillStyle = color; // Chữ cùng màu với viền
                        overlayCtx.font = "24px 'Plus Jakarta Sans'";
                        overlayCtx.fillText(name, x1 + 10, y1 - 12);
                    });
                }
            })
            .catch(err => console.error("Lỗi kết nối AI Backend:", err))
            .finally(() => {
                isProcessing = false; // Mở khóa cho nhịp quét tiếp theo
            });

        }, 1500); // 1.5 giây quét 1 lần (Có thể giảm xuống 1000 nếu mạng mạnh)
    </script>
</body>
</html>