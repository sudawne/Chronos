<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Cổng Phụ Check-in | {{ $meeting->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f172a; overflow: hidden; margin: 0; touch-action: none; }
        .bg-animated { position: absolute; inset: 0; z-index: -1; background: radial-gradient(circle at 50% 50%, #1e1b4b 0%, #0f172a 100%); }
        #videoElement { transform: scaleX(-1); }
        .camera-glow { box-shadow: 0 0 60px rgba(99, 102, 241, 0.15), inset 0 0 30px rgba(0,0,0,0.6); }
    </style>
</head>
<body class="h-screen w-full flex flex-col items-center justify-center relative text-white p-4">
    
    <div id="blink-alert" class="absolute top-28 md:top-32 left-1/2 -translate-x-1/2 z-[100] flex items-center gap-2 px-5 py-2.5 bg-rose-500/90 backdrop-blur-md text-white rounded-full shadow-xl opacity-0 translate-y-[-10px] transition-all duration-300 pointer-events-none border border-rose-400">
        <span class="material-symbols-outlined text-xl animate-pulse">visibility</span>
        <span class="text-sm font-bold tracking-wide" id="blink-text">Vui lòng chớp mắt để điểm danh!</span>
    </div>

    <div class="bg-animated"></div>

    <div class="absolute top-8 md:top-10 left-1/2 -translate-x-1/2 text-center z-10 w-full px-4">
        <div class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-500/20 text-indigo-300 rounded-full text-xs md:text-sm font-bold border border-indigo-500/30 mb-3 shadow-lg backdrop-blur-sm">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 animate-pulse"></span>
            STATION: <span class="uppercase tracking-widest">{{ $gateName }}</span>
        </div>
        <h1 class="text-2xl md:text-3xl font-extrabold text-white/95 truncate drop-shadow-md">{{ $meeting->title }}</h1>
    </div>

    <div class="relative w-80 h-80 md:w-[560px] md:h-[560px] lg:w-[640px] lg:h-[640px] rounded-full overflow-hidden border-[8px] border-slate-800/80 bg-black shrink-0 camera-glow z-10 transition-all duration-300" id="camera-container">
        <video id="videoElement" autoplay playsinline muted class="w-full h-full object-cover"></video>
        <div class="absolute inset-0 border-[6px] border-transparent rounded-full transition-colors duration-300" id="camera-border"></div>
    </div>

    <div id="guest-info-card" class="-mt-16 md:-mt-24 opacity-0 translate-y-8 transition-all duration-500 ease-out flex flex-col items-center bg-slate-900/70 backdrop-blur-2xl border border-white/10 p-6 md:p-10 rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.5)] w-[90%] max-w-lg text-center z-20">
        <div class="w-16 h-16 md:w-20 md:h-20 bg-emerald-500 text-white rounded-full flex items-center justify-center mb-4 md:mb-5 shadow-[0_0_30px_rgba(16,185,129,0.4)] border-4 border-slate-900">
            <span class="material-symbols-outlined text-4xl md:text-5xl">how_to_reg</span>
        </div>
        <h2 id="guest-name" class="text-3xl md:text-4xl font-black text-white mb-2 drop-shadow-md tracking-tight">Nguyễn Văn A</h2>
        <p id="guest-pos" class="text-indigo-200 font-semibold text-base md:text-lg mb-6">Đại biểu</p>
        <div class="inline-flex items-center gap-2.5 px-6 py-3 bg-black/50 rounded-full text-base font-bold text-white border border-slate-700/50 shadow-inner">
            <span class="material-symbols-outlined text-[22px] text-amber-400">chair</span>
            <span id="guest-seat">Ghế tự do</span>
        </div>
    </div>

    <canvas id="captureCanvas" style="display: none;"></canvas>

    <script>
        // CẤU HÌNH LIVENESS DETECT
        const REQUIRE_BLINK = {{ ($meeting->require_blink ?? false) ? 'true' : 'false' }};
        const blinkAlert = document.getElementById('blink-alert');
        let blinkHideTimeout = null;

        const video = document.getElementById('videoElement');
        const captureCanvas = document.getElementById('captureCanvas');
        const captureCtx = captureCanvas.getContext('2d');
        
        const infoCard = document.getElementById('guest-info-card');
        const gName = document.getElementById('guest-name');
        const gPos = document.getElementById('guest-pos');
        const gSeat = document.getElementById('guest-seat');
        const camBorder = document.getElementById('camera-border');

        let isProcessing = false;
        let hideCardTimeout;

        function showBlinkPrompt() {
            blinkAlert.classList.remove('opacity-0', 'translate-y-[-10px]');
            blinkAlert.classList.add('opacity-100', 'translate-y-0');
            clearTimeout(blinkHideTimeout);
            blinkHideTimeout = setTimeout(() => {
                blinkAlert.classList.remove('opacity-100', 'translate-y-0');
                blinkAlert.classList.add('opacity-0', 'translate-y-[-10px]');
            }, 3000);
        }

        if (navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { width: 1280, height: 720, facingMode: "user" }, audio: false })
                .then(function (stream) { video.srcObject = stream; })
                .catch(function (err) { alert("Không thể mở Camera. Vui lòng kiểm tra quyền!"); });
        }

        function showGuestInfo(name, position, seat, color) {
            camBorder.style.borderColor = color || '#10b981';
            setTimeout(() => camBorder.style.borderColor = 'transparent', 600);

            gName.innerText = name;
            gPos.innerText = position ? position : "Đại biểu tham dự";
            gSeat.innerText = seat ? seat : "Ghế tự do";

            infoCard.classList.remove('opacity-0', 'translate-y-8');
            
            clearTimeout(hideCardTimeout);
            hideCardTimeout = setTimeout(() => {
                infoCard.classList.add('opacity-0', 'translate-y-8');
            }, 3500);
        }

        function captureAndSendFrame() {
            if (!video.videoWidth || isProcessing) return;

            isProcessing = true;
            captureCanvas.width = video.videoWidth;
            captureCanvas.height = video.videoHeight;
            captureCtx.drawImage(video, 0, 0, captureCanvas.width, captureCanvas.height);

            const base64Image = captureCanvas.toDataURL('image/jpeg', 0.8);

            let formData = new FormData();
            formData.append('image_base64', base64Image);
            formData.append('meeting_id', {{ $meeting->id }});

            fetch('{{ config("app.ai_server_url") }}/nhan_dien', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.detections && data.detections.length > 0) {
                    let person = data.detections[0];
                    if (person.name !== "Khach La" && person.name !== "Unknown") {
                        // LOGIC LIVENESS (CHỚP MẮT)
                        if (REQUIRE_BLINK && !person.is_blinking) {
                            showBlinkPrompt();
                        } else {
                            // Ẩn thông báo chớp mắt và show info
                            blinkAlert.classList.remove('opacity-100', 'translate-y-0');
                            blinkAlert.classList.add('opacity-0', 'translate-y-[-10px]');
                            showGuestInfo(person.name, person.position, person.seat, person.color);
                        }
                    }
                }
            })
            .catch(err => console.error("Đang quét...", err))
            .finally(() => { isProcessing = false; });
        }

        setInterval(captureAndSendFrame, 1200);

        function sendHeartbeat() {
            let formData = new FormData();
            formData.append('gate_name', '{{ $gateName }}');
            fetch('/api/meetings/{{ $meeting->id }}/gate-heartbeat', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData
            }).catch(e => console.log("Heartbeat error")); 
        }

        sendHeartbeat();
        setInterval(sendHeartbeat, 4000); 
    </script>
</body>
</html>