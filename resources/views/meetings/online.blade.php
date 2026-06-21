<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cổng Phụ Check-in | {{ $meeting->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f172a; overflow: hidden; }
        /* Lật ngược video để soi gương tự nhiên */
        #videoElement { transform: scaleX(-1); }
        #overlayCanvas { transform: scaleX(-1); pointer-events: none; }
    </style>
</head>
<body class="h-screen w-full flex flex-col items-center justify-center relative bg-slate-900 text-white p-4">

    <div class="mb-4 text-center z-10">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-500/20 text-indigo-400 rounded-full text-sm font-bold border border-indigo-500/30 mb-2">
            <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
            STATION: CỔNG PHỤ ĐIỂM DANH
        </div>
        <h1 class="text-2xl font-extrabold text-white">{{ $meeting->title }}</h1>
    </div>

    <div class="relative rounded-3xl overflow-hidden border-4 border-slate-800 shadow-2xl bg-black w-full max-w-4xl aspect-video">
        <video id="videoElement" autoplay playsinline muted class="w-full h-full object-cover"></video>
        
        <canvas id="overlayCanvas" class="absolute inset-0 w-full h-full"></canvas>

        <div id="toast-notify" class="absolute bottom-6 left-1/2 -translate-x-1/2 bg-emerald-500 text-white px-6 py-3 rounded-2xl shadow-[0_10px_25px_rgba(16,185,129,0.5)] font-bold text-lg opacity-0 transition-all duration-300 translate-y-10 flex items-center gap-3">
            <span class="material-symbols-outlined">check_circle</span>
            <span id="toast-name">Đã điểm danh...</span>
        </div>
    </div>

    <canvas id="captureCanvas" style="display: none;"></canvas>

    <script>
        const video = document.getElementById('videoElement');
        const overlayCanvas = document.getElementById('overlayCanvas');
        const overlayCtx = overlayCanvas.getContext('2d');
        const captureCanvas = document.getElementById('captureCanvas');
        const captureCtx = captureCanvas.getContext('2d');
        const toast = document.getElementById('toast-notify');
        const toastName = document.getElementById('toast-name');

        let isProcessing = false;
        let toastTimeout;

        // Bật Camera
        if (navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { width: 1280, height: 720 }, audio: false })
                .then(function (stream) {
                    video.srcObject = stream;
                    // Chỉnh size canvas overlay khớp với video thực tế
                    video.onloadedmetadata = () => {
                        overlayCanvas.width = video.videoWidth;
                        overlayCanvas.height = video.videoHeight;
                    };
                })
                .catch(function (err) {
                    alert("Không thể mở Camera. Vui lòng kiểm tra quyền!");
                });
        }

        function captureAndSendFrame() {
            if (!video.videoWidth || isProcessing) return;

            isProcessing = true;
            captureCanvas.width = video.videoWidth;
            captureCanvas.height = video.videoHeight;
            captureCtx.drawImage(video, 0, 0, captureCanvas.width, captureCanvas.height);

            const base64Image = captureCanvas.toDataURL('image/jpeg', 0.8);

            // Dùng FormData để gửi ảnh (Tương thích với API FastAPI)
            let formData = new FormData();
            formData.append('image_base64', base64Image);
            formData.append('meeting_id', {{ $meeting->id }});

            fetch('http://localhost:8001/nhan_dien', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                // Xóa các khung vẽ cũ
                overlayCtx.clearRect(0, 0, overlayCanvas.width, overlayCanvas.height);

                if (data.status === 'success' && data.detections) {
                    data.detections.forEach(det => {
                        let [x1, y1, x2, y2] = det.box;
                        let color = det.color;
                        let name = det.name;

                        // Vẽ khung nhận diện quanh mặt
                        overlayCtx.beginPath();
                        overlayCtx.lineWidth = 4;
                        overlayCtx.strokeStyle = color;
                        overlayCtx.roundRect(x1, y1, x2 - x1, y2 - y1, 10);
                        overlayCtx.stroke();

                        // Nếu là khách thật (Không phải Khach La), vẽ tên lên
                        if (name !== "Khach La" && name !== "Unknown") {
                            overlayCtx.fillStyle = color;
                            overlayCtx.fillRect(x1, y1 - 40, x2 - x1, 40);
                            overlayCtx.fillStyle = "#ffffff";
                            overlayCtx.font = "bold 20px 'Plus Jakarta Sans'";
                            overlayCtx.fillText(name, x1 + 10, y1 - 12);

                            // Bật Toast hiển thị phía dưới
                            showToast(name);
                        }
                    });
                }
            })
            .catch(err => console.error("Đang quét...", err))
            .finally(() => {
                isProcessing = false;
            });
        }

        // Quét liên tục mỗi 1.2 giây cho cổng phụ
        setInterval(captureAndSendFrame, 1200);

        // Hiển thị thông báo khi có người qua cổng
        function showToast(name) {
            toastName.innerText = name + " - OK";
            toast.classList.remove('opacity-0', 'translate-y-10');
            
            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-10');
            }, 2500);
        }
    </script>
</body>
</html>