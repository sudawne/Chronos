<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Kiosk AI | {{ $meeting->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;600;800&family=Dancing+Script:wght@600;700&family=Montserrat:wght@400;700;900&family=Playfair+Display:ital,wght@0,600;0,800;1,600&family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
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

        .designed-element { position: absolute; white-space: nowrap; }
        .text-glow { transform: scale(1.02) !important; }
        .avatar-glow {
            box-shadow: 0 0 35px rgba(255, 255, 255, 0.8), 0 0 70px rgba(89, 73, 190, 0.6) !important;
            transform: scale(1.02);
        }
    </style>
</head>

<body class="h-screen w-full flex items-center justify-center relative">

    <div id="blink-alert" class="fixed top-8 left-1/2 transform -translate-x-1/2 z-[100] flex items-center gap-3 px-6 py-3 bg-rose-500/90 backdrop-blur-md text-white rounded-full shadow-[0_10px_25px_rgba(225,29,72,0.4)] opacity-0 translate-y-[-20px] transition-all duration-300 pointer-events-none border border-rose-400/50">
        <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
        <span class="font-bold tracking-wide" id="blink-text">Vui lòng chớp mắt để xác nhận!</span>
    </div>

    <div id="scale-wrapper" class="flex items-center justify-center w-full h-full">
        <div id="artboard" class="transition-all duration-500">
            @foreach($elements as $el)
                @php
                    $ex = ($el['x'] ?? 0) + $PAD_X;
                    $ey = ($el['y'] ?? 0) + $PAD_Y;
                @endphp

                @if(($el['type'] ?? 'text') == 'text')
                    @php
                        $align = $el['align'] ?? 'left';
                        $justify = $align == 'center' ? 'center' : ($align == 'right' ? 'flex-end' : 'flex-start');
                        $width = isset($el['width']) && $el['width'] !== 'max-content' && $el['width'] !== 'auto' ? $el['width'].'px' : 'max-content';
                        
                        $transformOrigin = $align == 'center' ? 'center center' : ($align == 'right' ? 'right center' : 'left center');
                    @endphp
                    
                    <div id="{{ $el['id'] }}" class="designed-element flex items-center" 
                        style="left: {{ $ex }}px; top: {{ $ey }}px; 
                               color: {{ $el['color'] ?? '#ffffff' }}; 
                               font-size: {{ $el['size'] ?? 24 }}px; 
                               font-weight: {{ $el['fontWeight'] ?? 'normal' }}; 
                               font-family: {{ $el['fontFamily'] ?? '\'Plus Jakarta Sans\', sans-serif' }}; 
                               width: {{ $width }}; 
                               text-align: {{ $align }}; 
                               justify-content: {{ $justify }}; 
                               transform-origin: {{ $transformOrigin }};">
                        {{ $el['content'] ?? $el['text'] }}
                    </div>
                @elseif(($el['type'] ?? '') == 'image')
                    <img id="{{ $el['id'] }}" src="{{ $el['src'] }}" class="designed-element" style="left: {{ $ex }}px; top: {{ $ey }}px; width: {{ $el['width'] }}px; max-width: calc({{ $ARTBOARD_W }}px - {{ $ex }}px);">
                @elseif(($el['type'] ?? '') == 'avatar')
                    <div id="{{ $el['id'] }}" class="designed-element flex items-center justify-center overflow-hidden transition-all duration-500" style="left: {{ $ex }}px; top: {{ $ey }}px; width: {{ $el['width'] ?? 200 }}px; height: {{ $el['width'] ?? 200 }}px; border-radius: 50%; border: {{ $el['borderWidth'] ?? 4 }}px solid {{ $el['borderColor'] ?? '#ffffff' }}; box-shadow: 0 10px 30px rgba(0,0,0,0.5); background: #000;">
                        <video id="live-video" autoplay playsinline muted style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); transition: opacity 0.6s ease-in-out;"></video>
                        <img id="recognized-avatar" src="" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; opacity: 0; transition: opacity 0.6s ease-in-out;">
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div id="control-buttons" class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 hidden transition-opacity duration-300">
        <button id="btn-wait" onclick="pauseWelcome()" title="Dừng màn hình" class="p-4 bg-amber-500 hover:bg-amber-600 text-white rounded-full shadow-[0_10px_20px_rgba(245,158,11,0.3)] transition-all flex items-center justify-center focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </button>
        <button id="btn-next" onclick="skipWelcome()" title="Tiếp tục ngay" class="p-4 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-[0_10px_20px_rgba(37,99,235,0.3)] transition-all flex items-center justify-center focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
        </button>
    </div>

    <canvas id="capture-canvas" style="display:none;"></canvas>

    <script>
        // KIỂM TRA SETTING CHỚP MẮT TỪ SERVER
        const REQUIRE_BLINK = {{ ($meeting->require_blink ?? false) ? 'true' : 'false' }};
        const blinkAlert = document.getElementById('blink-alert');
        const blinkText = document.getElementById('blink-text');
        let blinkHideTimeout = null;

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
        
        const controlButtons = document.getElementById('control-buttons');
        const btnWait = document.getElementById('btn-wait');

        let isProcessing = false;
        let isWelcoming = false;
        let welcomeTimeout = null; 

        [elName, elPosition, elSeat].forEach(el => {
            if(el) {
                el.dataset.defaultText = el.innerText;
                el.dataset.baseSize = el.style.fontSize; 
                el.style.transition = 'transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)'; 
            }
        });

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

        // HÀM HIỆN / ẨN THÔNG BÁO CHỚP MẮT
        function showBlinkPrompt(name) {
            blinkText.innerText = `Xin chào ${name}, vui lòng chớp mắt!`;
            blinkAlert.classList.remove('opacity-0', 'translate-y-[-20px]');
            blinkAlert.classList.add('opacity-100', 'translate-y-0');
            
            clearTimeout(blinkHideTimeout);
            blinkHideTimeout = setTimeout(() => { hideBlinkPrompt(); }, 3000);
        }

        function hideBlinkPrompt() {
            blinkAlert.classList.remove('opacity-100', 'translate-y-0');
            blinkAlert.classList.add('opacity-0', 'translate-y-[-20px]');
        }

        if (video) {
            navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 }, audio: false })
                .then(stream => { video.srcObject = stream; })
                .catch(err => { alert("Vui lòng cấp quyền Camera trên trình duyệt!"); });
        }

        async function captureAndSendFrame() {
    if (!video || !canvas || isProcessing || isWelcoming) return;

    isProcessing = true;
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    const base64Image = canvas.toDataURL('image/jpeg', 0.8);
    let formData = new FormData();
    formData.append('image_base64', base64Image);
    formData.append('meeting_id', {{ $meeting->id }});

    try {
        // 1. Gọi AI nhận diện (HTTPS)
        const res = await fetch('https://ai.chronos.io.vn/nhan_dien', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.status === 'success' && data.detections?.length > 0) {
            let person = data.detections[0];

            if (person.name && person.name !== "Unknown" && person.name !== "Khach La") {
                if (REQUIRE_BLINK && !person.is_blinking) {
                    showBlinkPrompt(person.name);
                } else {
                    hideBlinkPrompt();
                    
                    // HIỂN THỊ LỜI CHÀO NGAY (Cái cũ bạn làm rất tốt)
                    triggerWelcome({
                        name: person.name,
                        position: person.position || '',
                        seat: person.seat || '',
                        image_url: person.image_url || '/images/default-avatar.png'
                    });

                    // 2. GỬI ĐIỂM DANH BACKGROUND (Không chặn hiển thị)
                    // Thêm 'Accept': 'application/json' để Laravel hiểu đây là request API
                    fetch(`/api/meetings/{{ $meeting->id }}/checkin`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: new URLSearchParams({ 'name': person.name })
                    }).catch(err => console.log("Gửi điểm danh không thành công (nếu đã điểm danh rồi thì bỏ qua)"));
                }
            }
        }
    } catch (err) {
        // console.error("Quét AI...");
    } finally {
        isProcessing = false;
    }
}

        setInterval(captureAndSendFrame, 1500);

        function resetWelcomeScreen() {
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
            controlButtons.classList.add('hidden');
            btnWait.classList.replace('bg-slate-600', 'bg-amber-500');
            isWelcoming = false;
            hideBlinkPrompt();
        }

        function triggerWelcome(guest) {
            isWelcoming = true;
            clearTimeout(welcomeTimeout); 

            if (video && recognizedAvatar) {
                video.style.opacity = '0';
                recognizedAvatar.src = guest.image_url;
                recognizedAvatar.style.opacity = '1';
                if(avatarContainer) avatarContainer.classList.add('avatar-glow');
            }
            fillAndFitText(elName, guest.name);
            fillAndFitText(elPosition, guest.position ? guest.position : "");
            fillAndFitText(elSeat, guest.seat ? guest.seat : "");

            controlButtons.classList.remove('hidden');

            welcomeTimeout = setTimeout(() => { resetWelcomeScreen(); }, 10000); 
        }

        function pauseWelcome() {
            clearTimeout(welcomeTimeout); 
            btnWait.classList.replace('bg-amber-500', 'bg-slate-600');
        }

        function skipWelcome() {
            clearTimeout(welcomeTimeout);
            resetWelcomeScreen();
        }
    </script>
</body>
</html>