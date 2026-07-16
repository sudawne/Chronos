<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quét Mã QR | CHRONOS AI</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Chỉnh lại khung quét QR của thư viện cho đẹp, che các viền mặc định xấu xí */
        #reader { width: 100%; max-width: 500px; margin: 0 auto; border: none !important; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5); background-color: #1e293b; }
        #reader video { object-fit: cover; border-radius: 24px; }
        #reader__dashboard_section_csr span { color: white !important; }
        #reader__dashboard_section_swaplink { color: #f43f5e !important; text-decoration: none; font-weight: bold; margin-top: 10px; display: inline-block; }
        #reader button { background: #4f46e5; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; margin-top: 10px; transition: all 0.3s;}
        #reader button:hover { background: #4338ca; }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen flex flex-col items-center justify-center relative overflow-hidden">

    <div class="absolute top-8 left-0 right-0 text-center px-4 z-10">
        <h1 class="text-2xl font-extrabold text-white tracking-widest uppercase flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-rose-500">qr_code_scanner</span>
            CHRONOS SCANNER
        </h1>
        <p class="text-slate-400 text-sm mt-1">Đưa mã QR dự phòng vào khung hình để điểm danh</p>
        
        <p id="camera-warning" class="text-rose-400 text-xs font-semibold mt-3 hidden bg-rose-500/10 inline-block px-4 py-2 rounded-full border border-rose-500/20">
            ⚠️ Trình duyệt chặn Camera! Vui lòng cấp quyền (Allow) hoặc truy cập qua https/localhost
        </p>
    </div>

    <div class="w-full px-4 z-10 mt-16 md:mt-12">
        <div id="reader"></div>
    </div>

    <div id="status-box" class="absolute bottom-12 left-4 right-4 max-w-sm mx-auto p-5 rounded-2xl bg-slate-800/90 backdrop-blur-md border border-slate-700 shadow-2xl transform translate-y-32 opacity-0 transition-all duration-300 z-20 flex items-center gap-4">
        <div id="status-icon" class="w-12 h-12 rounded-full flex items-center justify-center shrink-0">
            </div>
        <div>
            <h3 id="status-title" class="font-bold text-lg leading-tight"></h3>
            <p id="status-msg" class="text-sm text-slate-300"></p>
        </div>
    </div>

    <script>
        const beepSound = new Audio('https://www.soundjay.com/buttons/beep-07a.mp3');

        function onScanSuccess(decodedText, decodedResult) {
            html5QrcodeScanner.pause();
            beepSound.play();

            try {
                // Giải mã chuỗi JSON từ QR (ví dụ: {"m":1,"g":15})
                const qrData = JSON.parse(decodedText);

                fetch('{{ route("api.process_qr") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(qrData)
                })
                .then(response => response.json())
                .then(data => {
                    showStatus(data);
                    
                    setTimeout(() => {
                        hideStatus();
                        html5QrcodeScanner.resume();
                    }, 2500);
                })
                .catch(error => {
                    showStatus({ status: 'error', message: 'Lỗi kết nối đến máy chủ điểm danh!' });
                    setTimeout(() => { hideStatus(); html5QrcodeScanner.resume(); }, 2500);
                });

            } catch (e) {
                showStatus({ status: 'error', message: 'Mã QR không hợp lệ đối với hệ thống này!' });
                setTimeout(() => { hideStatus(); html5QrcodeScanner.resume(); }, 2500);
            }
        }

        function onScanFailure(error) {
            // console.warn(`Code scan error = ${error}`);
        }

        function showStatus(data) {
            const box = document.getElementById('status-box');
            const icon = document.getElementById('status-icon');
            const title = document.getElementById('status-title');
            const msg = document.getElementById('status-msg');

            icon.className = 'w-12 h-12 rounded-full flex items-center justify-center shrink-0';

            if (data.status === 'success') {
                icon.classList.add('bg-emerald-500/20', 'text-emerald-500');
                icon.innerHTML = '<span class="material-symbols-outlined text-[28px]">check_circle</span>';
                title.innerText = data.name;
                title.className = 'font-bold text-lg leading-tight text-emerald-400';
                msg.innerText = data.position + ' - ' + data.message;
            } else if (data.status === 'warning') {
                icon.classList.add('bg-amber-500/20', 'text-amber-500');
                icon.innerHTML = '<span class="material-symbols-outlined text-[28px]">info</span>';
                title.innerText = 'Đã điểm danh';
                title.className = 'font-bold text-lg leading-tight text-amber-400';
                msg.innerText = data.message;
            } else {
                icon.classList.add('bg-rose-500/20', 'text-rose-500');
                icon.innerHTML = '<span class="material-symbols-outlined text-[28px]">error</span>';
                title.innerText = 'Lỗi truy xuất';
                title.className = 'font-bold text-lg leading-tight text-rose-400';
                msg.innerText = data.message;
            }

            box.classList.remove('translate-y-32', 'opacity-0');
        }

        function hideStatus() {
            document.getElementById('status-box').classList.add('translate-y-32', 'opacity-0');
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { fps: 10, qrbox: {width: 250, height: 250} },
            false
        );
        
        try {
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        } catch (e) {
            document.getElementById('camera-warning').classList.remove('hidden');
        }
    </script>
</body>
</html>