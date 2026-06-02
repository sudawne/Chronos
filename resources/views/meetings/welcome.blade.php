<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | {{ $meeting->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    
    @php
        $config = $meeting->welcome_config ? json_decode($meeting->welcome_config, true) : [];
        $bgColor = $config['bg_color'] ?? '#0f172a';
        $bgImage = $config['bg_image'] ?? '';
        $elements = $config['elements'] ?? [];
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
        .welcome-popup {
            opacity: 0; transform: translateY(100px) scale(0.9);
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .welcome-popup.show { opacity: 1; transform: translateY(0) scale(1); }
        #artboard { width: 960px; height: 540px; position: relative; }
        .designed-element { position: absolute; white-space: nowrap; text-shadow: 2px 4px 10px rgba(0,0,0,0.5); }
    </style>
</head>

<body class="h-screen w-full flex items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/30 -z-10 backdrop-blur-[2px]"></div>

    <div id="scale-wrapper" class="flex items-center justify-center w-full h-full">
        <div id="welcomeCard" class="welcome-popup hidden">
            <div id="artboard" class="bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl shadow-2xl">
                
                @foreach($elements as $el)
                    @if(($el['type'] ?? 'text') == 'text')
                        <div id="{{ $el['id'] }}" class="designed-element" 
                             style="left: {{ $el['x'] }}px; top: {{ $el['y'] }}px; color: {{ $el['color'] ?? '#ffffff' }}; font-size: {{ $el['size'] ?? 24 }}px; font-weight: {{ $el['fontWeight'] ?? 'normal' }};">
                            {{ $el['content'] ?? $el['text'] }}
                        </div>
                    @elseif(($el['type'] ?? '') == 'image')
                        <img src="{{ $el['src'] }}" class="designed-element" style="left: {{ $el['x'] }}px; top: {{ $el['y'] }}px; width: {{ $el['width'] }}px;">
                    @elseif(($el['type'] ?? '') == 'avatar')
                        <div id="{{ $el['id'] }}" class="designed-element flex items-center justify-center overflow-hidden" 
                             style="left: {{ $el['x'] }}px; top: {{ $el['y'] }}px; border-radius: 50%; border: {{ $el['borderWidth'] ?? 4 }}px solid {{ $el['borderColor'] ?? '#ffffff' }}; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                            <img src="https://ui-avatars.com/api/?name=Guest&size=300" style="width: {{ $el['width'] ?? 200 }}px; height: {{ $el['width'] ?? 200 }}px; object-fit: cover;">
                        </div>
                    @endif
                @endforeach

            </div>
        </div>
    </div>

    <script>
        function scaleArtboard() {
            const scale = Math.min(window.innerWidth / 1100, window.innerHeight / 650);
            document.getElementById('artboard').style.transform = `scale(${scale})`;
            document.getElementById('artboard').style.transformOrigin = 'center center';
        }
        window.addEventListener('resize', scaleArtboard);
        scaleArtboard();

        const welcomeCard = document.getElementById('welcomeCard');
        
        // Khai báo chuẩn xác các biến ID từ thiết kế
        const elName = document.getElementById('el_name');
        const elPosition = document.getElementById('el_position');
        const elSeat = document.getElementById('el_seat');
        const elAvatar = document.getElementById('el_avatar'); // Dòng này siêu quan trọng để sửa lỗi của bạn

        let lastGuestName = ""; 

        setInterval(() => {
            fetch(`/api/meetings/{{ $meeting->id }}/latest-checkin`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'found') {
                        if (data.guest.name !== lastGuestName) {
                            lastGuestName = data.guest.name;
                            
                            // 1. Cập nhật Text
                            if(elName) elName.innerText = data.guest.name;
                            if(elPosition) elPosition.innerText = data.guest.position ? "Chức vụ: " + data.guest.position : "";
                            if(elSeat) elSeat.innerText = data.guest.seat ? "Ghế: " + data.guest.seat : "";
                            
                            // 2. Cập nhật Khung Ảnh
                            if(elAvatar) {
                                const imgTag = elAvatar.querySelector('img');
                                if(imgTag) imgTag.src = data.guest.avatar;
                            }
                            
                            // 3. Hiển thị Màn hình
                            welcomeCard.classList.remove('hidden');
                            setTimeout(() => welcomeCard.classList.add('show'), 50);

                            // 4. Ẩn Màn hình sau 5 giây
                            setTimeout(() => {
                                welcomeCard.classList.remove('show');
                                setTimeout(() => welcomeCard.classList.add('hidden'), 600);
                                lastGuestName = ""; 
                            }, 5000);
                        }
                    }
                })
                .catch(err => console.error("Đang chờ AI Server...", err));
        }, 1500);
    </script>
</body>
</html>