<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Kiosk AI | {{ $meeting->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">

    @php
        $config = $meeting->welcome_config ? json_decode($meeting->welcome_config, true) : [];
        $bgColor = $config['bg_color'] ?? '#0f172a';
        $bgImage = $config['bg_image'] ?? '';
        $elements = $config['elements'] ?? [];

        $PAD_X = 8;
        $PAD_Y = 4;
        $ARTBOARD_W = 960;
        $ARTBOARD_H = 540;
    @endphp

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow: hidden;
            margin: 0;
            background-color: {{ $bgColor }};
            background-image: url('{{ $bgImage }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        #artboard {
            width: {{ $ARTBOARD_W }}px; height: {{ $ARTBOARD_H }}px;
            position: relative; margin: auto;
            overflow: hidden; 
            box-shadow: 0 0 0 1px rgba(0,0,0,0.05), 0 25px 50px -12px rgba(0,0,0,0.3);
        }

        .designed-element {
            position: absolute;
            /* ĐÃ XÓA HIỆU ỨNG ĐỔ BÓNG: Trả lại chữ sắc nét tuyệt đối như Design */
            white-space: nowrap; 
        }

        /* --- CÁC HIỆU ỨNG ANIMATION --- */
        .text-glow {
            /* ĐÃ XÓA HIỆU ỨNG LOANG MÀU (text-shadow) */
            /* Chỉ giữ lại hiệu ứng phóng to cực nhẹ (2%) để tạo nhịp điệu khi người dùng đi ngang qua */
            transform: scale(1.02) !important; 
        }

        .avatar-glow {
            box-shadow: 0 0 35px rgba(255, 255, 255, 0.8), 0 0 70px rgba(89, 73, 190, 0.6) !important;
            transform: scale(1.02);
        }
    </style>
</head>

<body class="h-screen w-full flex items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/30 -z-10 backdrop-blur-[2px]"></div>

    <div id="scale-wrapper" class="flex items-center justify-center w-full h-full">
        <div id="artboard" class="transition-all duration-500">

            @foreach($elements as $el)
                @php
                    $ex = ($el['x'] ?? 0) + $PAD_X;
                    $ey = ($el['y'] ?? 0) + $PAD_Y;
                @endphp

                @if(($el['type'] ?? 'text') == 'text')
                    <div id="{{ $el['id'] }}" class="designed-element"
                         style="left: {{ $ex }}px; top: {{ $ey }}px; color: {{ $el['color'] ?? '#ffffff' }}; font-size: {{ $el['size'] ?? 24 }}px; font-weight: {{ $el['fontWeight'] ?? 'normal' }}; transform-origin: left center;">
                        {{ $el['content'] ?? $el['text'] }}
                    </div>

                @elseif(($el['type'] ?? '') == 'image')
                    <img id="{{ $el['id'] }}" src="{{ $el['src'] }}" class="designed-element" style="left: {{ $ex }}px; top: {{ $ey }}px; width: {{ $el['width'] }}px; max-width: calc({{ $ARTBOARD_W }}px - {{ $ex }}px);">

                @elseif(($el['type'] ?? '') == 'avatar')
                    <div id="{{ $el['id'] }}" class="designed-element flex items-center justify-center overflow-hidden transition-all duration-500"
                         style="left: {{ $ex }}px; top: {{ $ey }}px; width: {{ $el['width'] ?? 200 }}px; height: {{ $el['width'] ?? 200 }}px; border-radius: 50%; border: {{ $el['borderWidth'] ?? 4 }}px solid {{ $el['borderColor'] ?? '#ffffff' }}; box-shadow: 0 10px 30px rgba(0,0,0,0.5); background: #000;">

                        <video id="live-video" autoplay playsinline muted
                               style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); transition: opacity 0.6s ease-in-out;"></video>

                        <img id="recognized-avatar" src=""
                             style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; opacity: 0; transition: opacity 0.6s ease-in-out;">
                    </div>
                @endif
            @endforeach

        </div>
    </div>

    <canvas id="capture-canvas" style="display:none;"></canvas>

    <script>
        const ARTBOARD_W = {{ $ARTBOARD_W }};
        const ARTBOARD_H = {{ $ARTBOARD_H }};

        function scaleArtboard() {
            const scale = Math.min(window.innerWidth / ARTBOARD_W, window.innerHeight / ARTBOARD_H) * 0.95;
            document.getElementById('artboard').style.transform = `scale(${scale})`;
        }
        window.addEventListener('resize', scaleArtboard);
        scaleArtboard();

        const video = document.getElementById('live-video');
        const recognizedAvatar = document.getElementById('recognized-avatar');
        const avatarContainer = document.getElementById('el_avatar');

        const elName = document.getElementById('el_name');
        const elPosition = document.getElementById('el_position');
        const elSeat = document.getElementById('el_seat');

        const canvas = document.getElementById('capture-canvas');
        const ctx = canvas ? canvas.getContext('2d') : null;

        let isProcessing = false;
        let isWelcoming = false;

        // Lưu lại chính xác Giao diện Mặc định lúc thiết kế
        [elName, elPosition, elSeat].forEach(el => {
            if(el) {
                el.dataset.defaultText = el.innerText;
                el.dataset.baseSize = el.style.fontSize; 
                // Xóa hiệu ứng text-shadow khỏi transition, chỉ giữ mượt transform
                el.style.transition = 'transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)'; 
            }
        });

        // HÀM XỬ LÝ CHỮ: Tự động co chữ nếu quá dài
        function fillAndFitText(el, text) {
            if (!el) return;
            
            el.innerText = text;
            el.style.fontSize = el.dataset.baseSize; 
            
            const maxAllowedWidth = ARTBOARD_W - el.offsetLeft - 20; 
            if (el.scrollWidth > maxAllowedWidth) {
                const ratio = maxAllowedWidth / el.scrollWidth;
                const baseSizeNum = parseFloat(el.dataset.baseSize);
                el.style.fontSize = (baseSizeNum * ratio) + 'px';
            }

            el.classList.add('text-glow');
        }

        if (video) {
            navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 }, audio: false })
                .then(stream => { video.srcObject = stream; })
                .catch(err => {
                    console.error("Lỗi Camera:", err);
                    alert("Vui lòng cấp quyền Camera trên trình duyệt!");
                });
        }

        function captureAndSendFrame() {
            if (!video || !canvas || isProcessing || isWelcoming) return;

            isProcessing = true;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const base64Image = canvas.toDataURL('image/jpeg', 0.8);

            let formData = new FormData();
            formData.append('image_base64', base64Image);
            formData.append('meeting_id', {{ $meeting->id }});

            fetch('http://localhost:8001/nhan_dien', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if(!res.ok) throw new Error("HTTP Status: " + res.status);
                return res.json();
            })
            .then(data => {
                if (data.status === 'success' && data.detections && data.detections.length > 0) {
                    let person = data.detections[0];

                    if (person.name && person.name !== "Unknown" && person.name !== "Khach La") {
                        triggerWelcome({
                            name: person.name,
                            position: person.position || '',
                            seat: person.seat || '',
                            image_url: person.image_url || '/images/default-avatar.png'
                        });
                    }
                }
            })
            .catch(err => console.error("Đang quét AI..."))
            .finally(() => { isProcessing = false; });
        }

        setInterval(captureAndSendFrame, 1500);

        function triggerWelcome(guest) {
            isWelcoming = true;

            if (video && recognizedAvatar) {
                video.style.opacity = '0';
                recognizedAvatar.src = guest.image_url;
                recognizedAvatar.style.opacity = '1';
                if(avatarContainer) avatarContainer.classList.add('avatar-glow');
            }

            fillAndFitText(elName, guest.name);
            fillAndFitText(elPosition, guest.position ? guest.position : "");
            fillAndFitText(elSeat, guest.seat ? guest.seat : "");

            setTimeout(() => {
                if (video && recognizedAvatar) {
                    recognizedAvatar.style.opacity = '0';
                    video.style.opacity = '1';
                    if(avatarContainer) avatarContainer.classList.remove('avatar-glow');
                }

                [elName, elPosition, elSeat].forEach(el => {
                    if(el) {
                        el.innerText = el.dataset.defaultText; 
                        el.style.fontSize = el.dataset.baseSize; 
                        el.classList.remove('text-glow');
                    }
                });

                isWelcoming = false;
            }, 10000); 
        }
    </script>
</body>
</html>