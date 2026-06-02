<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studio Thiết Kế | {{ $meeting->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; overflow: hidden; }
        .light-workspace { background-image: radial-gradient(#cbd5e1 1px, transparent 0); background-size: 20px 20px; }
        .dark .light-workspace { background-image: radial-gradient(#334155 1px, transparent 0); }
        
        #artboard {
            width: 960px; height: 540px;
            position: relative; overflow: hidden;
            background-color: {{ $config['bg_color'] ?? '#0f172a' }};
            background-image: url('{{ $config['bg_image'] ?? '' }}');
            background-size: cover; background-position: center;
            box-shadow: 0 0 0 1px rgba(0,0,0,0.05), 0 25px 50px -12px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }

        .draggable {
            position: absolute; cursor: grab; user-select: none;
            padding: 4px 8px; border: 2px dashed transparent;
        }
        .draggable:hover { border-color: rgba(156, 163, 175, 0.5); }
        .draggable:active { cursor: grabbing; }

        .selected-element {
            border-color: #6366f1 !important;
            background: rgba(99, 102, 241, 0.15);
            z-index: 50;
        }
        .draggable img { pointer-events: none; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 10px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
    </style>
</head>
<body class="h-screen flex flex-col bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-100 selection:bg-indigo-500/30">

    <header class="h-14 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 flex items-center justify-between px-4 shrink-0 z-20 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('meetings.show', $meeting->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </a>
            <div class="h-4 w-px bg-slate-300 dark:bg-slate-700"></div>
            <div>
                <h1 class="font-bold text-sm">CHRONOS Studio</h1>
                <p class="text-[11px] text-indigo-600 dark:text-indigo-400">{{ $meeting->title }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="zoomOut()" class="p-1.5 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white"><span class="material-symbols-outlined">zoom_out</span></button>
            <span id="zoom-level" class="text-xs font-bold w-10 text-center">100%</span>
            <button onclick="zoomIn()" class="p-1.5 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white"><span class="material-symbols-outlined">zoom_in</span></button>
            <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 mx-2"></div>
            <button onclick="saveDesign()" id="btn-save" class="flex items-center gap-2 px-5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-md transition-all">
                <span class="material-symbols-outlined text-[18px]">cloud_upload</span> Lưu thiết kế
            </button>
        </div>
    </header>

    <main class="flex-1 flex overflow-hidden">
        <aside class="w-72 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col shrink-0 z-10 overflow-y-auto">
            
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 bg-amber-50/50 dark:bg-amber-900/10">
                <h2 class="text-xs font-black uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">auto_awesome_mosaic</span> Mẫu thiết kế sẵn
                </h2>
                <div class="grid grid-cols-2 gap-2">
                    <button onclick="applyTemplate('dai_hoi')" class="relative h-16 rounded-xl border-2 border-transparent hover:border-amber-500 overflow-hidden group transition-all">
                        <div class="absolute inset-0 bg-gradient-to-br from-red-700 to-red-900"></div>
                        <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-yellow-300 drop-shadow-md">Đại Hội</span>
                    </button>
                    <button onclick="applyTemplate('hoi_nghi')" class="relative h-16 rounded-xl border-2 border-transparent hover:border-blue-500 overflow-hidden group transition-all">
                        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 to-blue-900"></div>
                        <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-white drop-shadow-md">Công Nghệ</span>
                    </button>
                </div>
            </div>

            <div class="p-5 border-b border-slate-200 dark:border-slate-800 bg-indigo-50/50 dark:bg-indigo-900/10">
                <h2 class="text-xs font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400 mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">smart_toy</span> Dữ liệu tự động (AI)
                </h2>
                <div class="flex flex-col gap-2">
                    <button onclick="addDynamicElement('el_name', 'Tên Đại Biểu', 64, '#ffffff')" class="flex items-center gap-2 p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold hover:border-indigo-400 transition-colors">
                        <span class="material-symbols-outlined text-[18px] text-indigo-500">person</span> Thêm [Tên Đại Biểu]
                    </button>
                    <button onclick="addDynamicElement('el_position', 'Chức vụ: ...', 20, '#cbd5e1')" class="flex items-center gap-2 p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold hover:border-indigo-400 transition-colors">
                        <span class="material-symbols-outlined text-[18px] text-emerald-500">work</span> Thêm [Chức vụ]
                    </button>
                    <button onclick="addDynamicElement('el_seat', 'Ghế: ...', 24, '#10b981')" class="flex items-center gap-2 p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold hover:border-indigo-400 transition-colors">
                        <span class="material-symbols-outlined text-[18px] text-amber-500">chair</span> Thêm [Ghế ngồi]
                    </button>
                    <button onclick="addAvatarElement()" class="flex items-center gap-2 p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-bold hover:border-indigo-400 transition-colors">
                        <span class="material-symbols-outlined text-[18px] text-rose-500">account_circle</span> Thêm [Khung Ảnh]
                    </button>
                </div>
            </div>

            <div class="p-5 flex-1">
                <h2 class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4">Trang trí tĩnh</h2>
                <div class="mb-4">
                    <label class="text-[13px] font-semibold block mb-1">Màu nền mặc định</label>
                    <input type="color" id="bgColor" class="w-10 h-10 rounded-lg cursor-pointer bg-slate-50 dark:bg-slate-800 border-none p-1" value="{{ $config['bg_color'] ?? '#0f172a' }}">
                </div>
                <div class="mb-5">
                    <label class="text-[13px] font-semibold block mb-1">Ảnh nền sân khấu</label>
                    <label class="flex flex-col items-center justify-center w-full h-20 border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-xl cursor-pointer hover:border-indigo-500 bg-slate-50 dark:bg-slate-800 transition-all">
                        <span class="material-symbols-outlined text-slate-400">add_photo_alternate</span>
                        <span class="text-xs text-slate-500">Tải ảnh nền lên</span>
                        <input type="file" class="hidden" accept="image/*" onchange="handleBgUpload(this)">
                    </label>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <button onclick="addText()" class="flex flex-col items-center justify-center gap-1 h-16 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-indigo-400 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-xl text-slate-500">title</span>
                        <span class="text-xs font-semibold">Chữ tự do</span>
                    </button>
                    <label class="flex flex-col items-center justify-center gap-1 h-16 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-indigo-400 rounded-xl transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-xl text-slate-500">image</span>
                        <span class="text-xs font-semibold">Thêm Logo</span>
                        <input type="file" class="hidden" accept="image/*" onchange="addImage(this)">
                    </label>
                </div>
            </div>
        </aside>

        <section id="workspace" class="flex-1 flex items-center justify-center overflow-auto p-12 relative bg-slate-200 dark:bg-slate-950 light-workspace">
            <div id="artboard">
                @php
                    $elements = $config['elements'] ?? [];
                @endphp

                @foreach($elements as $el)
                    @if(($el['type'] ?? 'text') == 'text')
                        <div id="{{ $el['id'] }}" class="draggable" data-type="text"
                             style="left: {{ $el['x'] }}px; top: {{ $el['y'] }}px; color: {{ $el['color'] }}; font-size: {{ $el['size'] }}px; font-weight: {{ $el['fontWeight'] ?? 'normal' }};">
                            {{ $el['content'] ?? $el['text'] }}
                        </div>
                    @elseif(($el['type'] ?? '') == 'image')
                        <div id="{{ $el['id'] }}" class="draggable" data-type="image" style="left: {{ $el['x'] }}px; top: {{ $el['y'] }}px;">
                            <img src="{{ $el['src'] }}" style="width: {{ $el['width'] }}px;">
                        </div>
                    @elseif(($el['type'] ?? '') == 'avatar')
                        <div id="{{ $el['id'] }}" class="draggable" data-type="avatar" style="left: {{ $el['x'] }}px; top: {{ $el['y'] }}px; border-radius: 50%; overflow: hidden; border: {{ $el['borderWidth'] ?? 4 }}px solid {{ $el['borderColor'] ?? '#ffffff' }}; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
                            <img src="https://ui-avatars.com/api/?name=Avatar&size=300" style="width: {{ $el['width'] ?? 200 }}px; height: {{ $el['width'] ?? 200 }}px; object-fit: cover;">
                        </div>
                    @endif
                @endforeach
            </div>
        </section>

        <aside class="w-80 border-l border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col shrink-0 z-10 shadow-xl">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800">
                <h2 class="text-xs font-black uppercase tracking-widest text-slate-500 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">tune</span> Điều chỉnh
                </h2>
            </div>
            <div class="p-5 flex-1 overflow-y-auto">
                <div id="no-selection" class="flex flex-col items-center justify-center h-full text-slate-400">
                    <span class="material-symbols-outlined text-5xl mb-2">touch_app</span>
                    <p class="text-sm text-center">Bấm vào một chữ/ảnh<br>trên khung vẽ để sửa</p>
                </div>
                
                <div id="prop-text-panel" class="hidden space-y-5">
                    <div>
                        <label class="text-[13px] font-semibold block mb-1">Nội dung</label>
                        <textarea id="prop-text" rows="2" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg p-2.5 text-sm outline-none resize-none"></textarea>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="text-[13px] font-semibold block mb-1">Màu sắc</label>
                            <input type="color" id="prop-color" class="w-full h-10 rounded-lg cursor-pointer bg-slate-50 dark:bg-slate-800 border-none p-1">
                        </div>
                        <div class="flex-1">
                            <label class="text-[13px] font-semibold block mb-1">Kích cỡ</label>
                            <input type="number" id="prop-size" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg p-2 text-sm outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-[13px] font-semibold block mb-1">Độ đậm</label>
                        <select id="prop-weight" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg p-2.5 text-sm outline-none">
                            <option value="400">Bình thường (400)</option>
                            <option value="600">Bán đậm (600)</option>
                            <option value="800">Đậm (800)</option>
                        </select>
                    </div>
                </div>

                <div id="prop-action-panel" class="hidden mt-8 pt-5 border-t border-slate-200 dark:border-slate-800">
                    <button onclick="deleteSelected()" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold rounded-lg transition-colors border border-rose-200">
                        <span class="material-symbols-outlined text-[18px]">delete</span> Xóa thành phần này
                    </button>
                </div>
            </div>
        </aside>
    </main>

    <input type="hidden" id="bgImage" value="{{ $config['bg_image'] ?? '' }}">

    <script>
        const artboard = document.getElementById('artboard');
        let selectedElement = null;
        let isDragging = false;
        let startX, startY, initialX, initialY;
        let currentZoom = 1;

        function updateZoom() {
            artboard.style.transform = `scale(${currentZoom})`;
            document.getElementById('zoom-level').innerText = Math.round(currentZoom * 100) + '%';
        }
        function zoomIn() { if(currentZoom < 2) { currentZoom += 0.1; updateZoom(); } }
        function zoomOut() { if(currentZoom > 0.4) { currentZoom -= 0.1; updateZoom(); } }

        document.getElementById('bgColor').addEventListener('input', (e) => artboard.style.backgroundColor = e.target.value);
        function handleBgUpload(input) {
            if(input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const base64 = e.target.result;
                    artboard.style.backgroundImage = `url('${base64}')`;
                    document.getElementById('bgImage').value = base64;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // --- KHUNG ẢNH ĐẠI BIỂU ---
        function addAvatarElement() {
            if (document.getElementById('el_avatar')) {
                alert('Khung ảnh đã tồn tại!');
                selectElement(document.getElementById('el_avatar'));
                return;
            }
            const el = document.createElement('div');
            el.id = 'el_avatar';
            el.className = 'draggable';
            el.dataset.type = 'avatar';
            el.style.left = '100px';
            el.style.top = '150px';
            el.style.borderRadius = '50%';
            el.style.overflow = 'hidden';
            el.style.border = '6px solid #ffffff';
            el.style.boxShadow = '0 10px 25px rgba(0,0,0,0.3)';
            
            const img = document.createElement('img');
            img.src = 'https://ui-avatars.com/api/?name=Avatar&size=300';
            img.style.width = '240px';
            img.style.height = '240px';
            img.style.objectFit = 'cover';
            
            el.appendChild(img);
            attachEvents(el);
            artboard.appendChild(el);
            selectElement(el);
        }

        // --- TEMPLATE ---
        const templates = {
            'dai_hoi': {
                bg_color: '#a80000',
                elements: [
                    { id: 'el_avatar', type: 'avatar', x: 80, y: 120, width: 280, borderWidth: 8, borderColor: '#fcd34d' },
                    { id: 'el_welcome', type: 'text', content: 'CHÀO MỪNG\nĐồng chí', x: 450, y: 150, color: '#fcd34d', size: 36, fontWeight: '800' },
                    { id: 'el_name', type: 'text', content: 'TRỊNH HÙNG SƠN', x: 450, y: 250, color: '#ffffff', size: 54, fontWeight: '800' },
                    { id: 'el_position', type: 'text', content: 'Đại biểu tham dự Đại hội', x: 450, y: 350, color: '#fef3c7', size: 24, fontWeight: '600' },
                    { id: 'el_seat', type: 'text', content: 'Số ghế ngồi: 04', x: 130, y: 430, color: '#ffffff', size: 22, fontWeight: '800' }
                ]
            },
            'hoi_nghi': {
                bg_color: '#0f172a',
                elements: [
                    { id: 'el_avatar', type: 'avatar', x: 600, y: 140, width: 250, borderWidth: 4, borderColor: '#3b82f6' },
                    { id: 'el_welcome', type: 'text', content: 'WELCOME', x: 100, y: 160, color: '#60a5fa', size: 28, fontWeight: '600' },
                    { id: 'el_name', type: 'text', content: 'Alex Ferguson', x: 100, y: 210, color: '#ffffff', size: 64, fontWeight: '800' },
                    { id: 'el_position', type: 'text', content: 'Chief Technology Officer', x: 100, y: 310, color: '#94a3b8', size: 24, fontWeight: '400' },
                    { id: 'el_seat', type: 'text', content: 'VIP SEAT: A-01', x: 100, y: 380, color: '#10b981', size: 20, fontWeight: '800' }
                ]
            }
        };

        function applyTemplate(theme) {
            if(!confirm('Áp dụng Template sẽ xóa thiết kế hiện tại. Tiếp tục?')) return;
            const data = templates[theme];
            
            document.getElementById('bgColor').value = data.bg_color;
            artboard.style.backgroundColor = data.bg_color;
            artboard.style.backgroundImage = 'none';
            document.getElementById('bgImage').value = '';
            document.querySelectorAll('.draggable').forEach(el => el.remove());

            data.elements.forEach(elData => {
                const el = document.createElement('div');
                el.id = elData.id;
                el.className = 'draggable';
                el.dataset.type = elData.type;
                el.style.left = elData.x + 'px';
                el.style.top = elData.y + 'px';

                if(elData.type === 'text') {
                    el.style.color = elData.color;
                    el.style.fontSize = elData.size + 'px';
                    el.style.fontWeight = elData.fontWeight;
                    el.innerText = elData.content;
                } else if(elData.type === 'avatar') {
                    el.style.borderRadius = '50%';
                    el.style.overflow = 'hidden';
                    el.style.border = `${elData.borderWidth}px solid ${elData.borderColor}`;
                    el.style.boxShadow = '0 10px 25px rgba(0,0,0,0.3)';
                    const img = document.createElement('img');
                    img.src = 'https://ui-avatars.com/api/?name=Avatar&size=300';
                    img.style.width = elData.width + 'px';
                    img.style.height = elData.width + 'px';
                    img.style.objectFit = 'cover';
                    el.appendChild(img);
                }
                attachEvents(el);
                artboard.appendChild(el);
            });
        }

        // --- CÁC HÀM CƠ BẢN ---
        function addDynamicElement(id, defaultText, defaultSize, defaultColor) {
            if (document.getElementById(id)) { selectElement(document.getElementById(id)); return; }
            const el = document.createElement('div');
            el.id = id; el.className = 'draggable'; el.dataset.type = 'text';
            el.style.left = '350px'; el.style.top = '250px';
            el.style.color = defaultColor; el.style.fontSize = defaultSize + 'px'; el.style.fontWeight = '800';
            el.innerText = defaultText;
            attachEvents(el); artboard.appendChild(el); selectElement(el);
        }

        function addText() { addDynamicElement('el_' + Date.now(), 'Nhập chữ vào đây', 32, '#ffffff'); }
        function addImage(input) {
            if(input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const el = document.createElement('div');
                    el.id = 'el_' + Date.now(); el.className = 'draggable'; el.dataset.type = 'image';
                    el.style.left = '300px'; el.style.top = '100px';
                    const img = document.createElement('img'); img.src = e.target.result; img.style.width = '200px';
                    el.appendChild(img); attachEvents(el); artboard.appendChild(el); selectElement(el); input.value = '';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function attachEvents(el) {
            el.addEventListener('mousedown', dragStart); el.addEventListener('touchstart', dragStart, {passive: false});
        }
        document.querySelectorAll('.draggable').forEach(el => attachEvents(el));
        artboard.addEventListener('mousedown', (e) => { if (e.target === artboard) deselectElement(); });

        function dragStart(e) {
            if(e.type === 'mousedown' && e.button !== 0) return; 
            if(e.type === 'touchstart') e.preventDefault();
            selectElement(this); isDragging = true;
            let clientX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
            let clientY = e.type.includes('mouse') ? e.clientY : e.touches[0].clientY;
            startX = clientX / currentZoom; startY = clientY / currentZoom;
            initialX = selectedElement.offsetLeft; initialY = selectedElement.offsetTop;
            document.addEventListener('mousemove', drag); document.addEventListener('mouseup', dragEnd);
            document.addEventListener('touchmove', drag, {passive: false}); document.addEventListener('touchend', dragEnd);
        }
        function drag(e) {
            if (!isDragging) return; e.preventDefault();
            let clientX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
            let clientY = e.type.includes('mouse') ? e.clientY : e.touches[0].clientY;
            selectedElement.style.left = `${initialX + (clientX / currentZoom) - startX}px`;
            selectedElement.style.top = `${initialY + (clientY / currentZoom) - startY}px`;
        }
        function dragEnd() {
            isDragging = false; document.removeEventListener('mousemove', drag); document.removeEventListener('mouseup', dragEnd);
            document.removeEventListener('touchmove', drag); document.removeEventListener('touchend', dragEnd);
        }

        function selectElement(el) {
            deselectElement(); selectedElement = el; selectedElement.classList.add('selected-element');
            document.getElementById('no-selection').classList.add('hidden');
            document.getElementById('prop-action-panel').classList.remove('hidden');
            
            if (el.dataset.type === 'text') {
                document.getElementById('prop-text-panel').classList.remove('hidden');
                document.getElementById('prop-text').value = el.innerText;
                document.getElementById('prop-color').value = rgbToHex(window.getComputedStyle(el).color);
                document.getElementById('prop-size').value = parseInt(window.getComputedStyle(el).fontSize);
                document.getElementById('prop-weight').value = window.getComputedStyle(el).fontWeight;
                document.getElementById('prop-text').disabled = (el.id === 'el_name' || el.id === 'el_position' || el.id === 'el_seat');
            }
        }
        function deselectElement() {
            if(selectedElement) selectedElement.classList.remove('selected-element');
            selectedElement = null;
            document.getElementById('no-selection').classList.remove('hidden');
            document.getElementById('prop-text-panel').classList.add('hidden');
            document.getElementById('prop-action-panel').classList.add('hidden');
        }
        function deleteSelected() { if(selectedElement) { selectedElement.remove(); deselectElement(); } }
        document.addEventListener('keydown', (e) => { if(e.key === 'Delete' && selectedElement && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') deleteSelected(); });

        document.getElementById('prop-text').addEventListener('input', (e) => { if(selectedElement && !e.target.disabled) selectedElement.innerText = e.target.value; });
        document.getElementById('prop-color').addEventListener('input', (e) => { if(selectedElement) selectedElement.style.color = e.target.value; });
        document.getElementById('prop-size').addEventListener('input', (e) => { if(selectedElement) selectedElement.style.fontSize = `${e.target.value}px`; });
        document.getElementById('prop-weight').addEventListener('change', (e) => { if(selectedElement) selectedElement.style.fontWeight = e.target.value; });

        function rgbToHex(rgb) {
            let match = rgb.match(/\d+/g); if(!match) return "#ffffff";
            let [r, g, b] = match; return "#" + (1 << 24 | r << 16 | g << 8 | b).toString(16).slice(1);
        }

        function saveDesign() {
            const btnSave = document.getElementById('btn-save');
            btnSave.innerHTML = `<span class="material-symbols-outlined animate-spin">refresh</span> Đang lưu...`;
            
            const elementsData = [];
            document.querySelectorAll('.draggable').forEach(el => {
                const type = el.dataset.type || 'text';
                let data = { id: el.id, type: type, x: el.offsetLeft, y: el.offsetTop };
                if (type === 'text') {
                    data.content = el.innerText;
                    data.color = rgbToHex(window.getComputedStyle(el).color);
                    data.size = parseInt(window.getComputedStyle(el).fontSize);
                    data.fontWeight = window.getComputedStyle(el).fontWeight;
                } else if (type === 'image') {
                    data.src = el.querySelector('img').src;
                    data.width = parseInt(el.querySelector('img').style.width);
                } else if (type === 'avatar') {
                    data.width = parseInt(el.querySelector('img').style.width) || 200;
                    data.borderWidth = parseInt(window.getComputedStyle(el).borderWidth) || 0;
                    data.borderColor = rgbToHex(window.getComputedStyle(el).borderColor);
                }
                elementsData.push(data);
            });

            fetch('{{ route('api.save_design', $meeting->id) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ bg_color: document.getElementById('bgColor').value, bg_image: document.getElementById('bgImage').value, elements: elementsData })
            }).then(res => res.json()).then(data => {
                btnSave.innerHTML = `Đã lưu xong!`; btnSave.classList.replace('bg-indigo-600', 'bg-emerald-500');
                setTimeout(() => { btnSave.innerHTML = `Lưu thiết kế`; btnSave.classList.replace('bg-emerald-500', 'bg-indigo-600'); }, 2000);
            });
        }
    </script>
</body>
</html>