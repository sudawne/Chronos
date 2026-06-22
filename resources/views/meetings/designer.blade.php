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
            <button onclick="promptSaveTemplate()" class="flex items-center gap-2 px-4 py-1.5 bg-emerald-100 hover:bg-emerald-600 text-emerald-700 hover:text-white text-sm font-bold rounded-lg shadow-sm transition-all border border-emerald-200">
                <span class="material-symbols-outlined text-[18px]">auto_awesome_mosaic</span> Lưu làm Mẫu
            </button>
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
                <div id="template-list" class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-1">
                    <div class="col-span-2 text-center text-xs text-slate-500 py-4 animate-pulse">Đang tải thư viện mẫu...</div>
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
                <div id="prop-avatar-panel" class="hidden space-y-5">
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="text-[13px] font-semibold block mb-1">Kích cỡ ảnh</label>
                            <input type="number" id="prop-avatar-size" ...>
                        </div>
                        <div class="flex-1">
                            <label class="text-[13px] font-semibold block mb-1">Độ dày viền</label>
                            <input type="number" id="prop-avatar-border-width" ...>
                        </div>
                    </div>
                    <div>
                        <label class="text-[13px] font-semibold block mb-1">Màu viền ảnh</label>
                        <input type="color" id="prop-avatar-border-color" ...>
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
                document.getElementById('prop-avatar-panel').classList.add('hidden');
                document.getElementById('prop-text').value = el.innerText;
                document.getElementById('prop-color').value = rgbToHex(window.getComputedStyle(el).color);
                document.getElementById('prop-size').value = parseInt(window.getComputedStyle(el).fontSize);
                document.getElementById('prop-weight').value = window.getComputedStyle(el).fontWeight;
                document.getElementById('prop-text').disabled = (el.id === 'el_name' || el.id === 'el_position' || el.id === 'el_seat');
            }
            else if (el.dataset.type === 'avatar') {
                // 1. Hiện bảng Avatar, giấu bảng Text
                document.getElementById('prop-avatar-panel').classList.remove('hidden');
                document.getElementById('prop-text-panel').classList.add('hidden');
                
                // 2. Hút dữ liệu hiện tại gán vào input
                document.getElementById('prop-avatar-size').value = parseInt(window.getComputedStyle(el).width) || 200;
                document.getElementById('prop-avatar-border-width').value = parseInt(window.getComputedStyle(el).borderWidth) || 0;
                document.getElementById('prop-avatar-border-color').value = rgbToHex(window.getComputedStyle(el).borderColor);
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

        // --- SỰ KIỆN ĐIỀU CHỈNH AVATAR ---
        document.getElementById('prop-avatar-size').addEventListener('input', (e) => { 
            if(selectedElement && selectedElement.dataset.type === 'avatar') {
                const size = `${e.target.value}px`;
                selectedElement.style.width = size;
                selectedElement.style.height = size;
            }
        });
        
        document.getElementById('prop-avatar-border-width').addEventListener('input', (e) => { 
            if(selectedElement && selectedElement.dataset.type === 'avatar') {
                selectedElement.style.borderWidth = `${e.target.value}px`;
            }
        });
        
        document.getElementById('prop-avatar-border-color').addEventListener('input', (e) => { 
            if(selectedElement && selectedElement.dataset.type === 'avatar') {
                selectedElement.style.borderColor = e.target.value;
            }
        });
        
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

        //Lưu template
        let globalTemplates = [];

        // 1. Tự động tải danh sách Mẫu từ Database khi vừa vào trang
        document.addEventListener('DOMContentLoaded', loadGlobalTemplates);

        function loadGlobalTemplates() {
            fetch('{{ route('api.get_templates') }}')
            .then(res => res.json())
            .then(data => {
                globalTemplates = data;
                renderTemplates();
            });
        }

        // 2. Vẽ danh sách nút Mẫu ra màn hình (ĐÃ CẬP NHẬT GIAO DIỆN NÚT XÓA)
        // 2. Vẽ danh sách nút Mẫu ra màn hình (HIỂN THỊ XEM TRƯỚC TRỰC QUAN)
        function renderTemplates() {
            const container = document.getElementById('template-list');
            if (globalTemplates.length === 0) {
                container.innerHTML = `<div class="col-span-2 text-center text-xs text-slate-500 py-4">Chưa có mẫu nào. Hãy thiết kế và lưu lại!</div>`;
                return;
            }

            let html = '';
            globalTemplates.forEach(tpl => {
                // Đọc dữ liệu thiết kế từ Database
                const data = typeof tpl.config === 'string' ? JSON.parse(tpl.config) : tpl.config;
                
                // --- 1. Phục dựng Ảnh nền / Màu nền ---
                let bgStyle = '';
                if (data.bg_image) {
                    bgStyle = `background-image: url('${data.bg_image}'); background-size: cover; background-position: center;`;
                } else {
                    bgStyle = `background-color: ${data.bg_color || '#0f172a'};`;
                }

                // --- 2. Phục dựng các thẻ chữ, hình ảnh, khung avatar ---
                let innerHtml = '';
                if (data.elements) {
                    data.elements.forEach(el => {
                        if (el.type === 'text') {
                            innerHtml += `<div style="position: absolute; left: ${el.x}px; top: ${el.y}px; color: ${el.color || '#ffffff'}; font-size: ${el.size}px; font-weight: ${el.fontWeight || 'normal'}; white-space: nowrap; text-shadow: 2px 4px 10px rgba(0,0,0,0.5);">${el.content || el.text || ''}</div>`;
                        } else if (el.type === 'image') {
                            innerHtml += `<img src="${el.src}" style="position: absolute; left: ${el.x}px; top: ${el.y}px; width: ${el.width}px; height: auto;">`;
                        } else if (el.type === 'avatar') {
                            innerHtml += `<div style="position: absolute; left: ${el.x}px; top: ${el.y}px; width: ${el.width || 200}px; height: ${el.width || 200}px; border-radius: 50%; border: ${el.borderWidth || 0}px solid ${el.borderColor || 'transparent'}; background: rgba(0,0,0,0.4); backdrop-filter: blur(4px);"></div>`;
                        }
                    });
                }

                // --- 3. Gói tất cả vào một Mini-Artboard và thu nhỏ bằng CSS Scale ---
                html += `
                    <div class="relative rounded-xl border border-slate-300 hover:border-indigo-500 overflow-hidden group transition-all shadow-sm bg-slate-100" style="width: 120px; height: 67.5px;">
                        
                        <div class="absolute top-0 left-0 w-[960px] h-[540px] pointer-events-none" style="transform: scale(0.125); transform-origin: top left; ${bgStyle}">
                            ${innerHtml}
                        </div>
                        
                        <button onclick="applyTemplate(${tpl.id})" class="absolute inset-0 w-full h-full bg-slate-900/30 hover:bg-slate-900/10 transition-colors focus:outline-none flex items-end justify-center pb-1 z-10">
                            <span class="text-[9px] font-bold text-white px-2 py-0.5 bg-black/60 rounded backdrop-blur-sm truncate max-w-[95%] shadow-sm leading-tight">${tpl.name}</span>
                        </button>
                        
                        <button onclick="deleteTemplate(${tpl.id})" class="absolute top-1 right-1 w-5 h-5 bg-white/90 hover:bg-rose-500 rounded flex items-center justify-center text-rose-600 hover:text-white opacity-0 group-hover:opacity-100 transition-all z-20 shadow-sm" title="Xóa mẫu này">
                            <span class="material-symbols-outlined text-[13px]">delete</span>
                        </button>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        // THÊM HÀM MỚI: XỬ LÝ XÓA MẪU
        function deleteTemplate(id) {
            if(!confirm('Bạn có chắc chắn muốn xóa vĩnh viễn mẫu thiết kế này không?')) return;

            fetch(`/api/welcome-templates/${id}`, {
                method: 'DELETE',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    loadGlobalTemplates(); // Xóa xong tự động load lại danh sách
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Có lỗi xảy ra khi xóa mẫu!');
            });
        }

        // 3. Hàm Áp dụng Mẫu
        function applyTemplate(templateId) {
            if(!confirm('Áp dụng Template sẽ ghi đè thiết kế hiện tại. Tiếp tục?')) return;
            
            const tpl = globalTemplates.find(t => t.id === templateId);
            if (!tpl) return;

            const data = typeof tpl.config === 'string' ? JSON.parse(tpl.config) : tpl.config;
            
            document.getElementById('bgColor').value = data.bg_color || '#0f172a';
            artboard.style.backgroundColor = data.bg_color || '#0f172a';
            
            if (data.bg_image) {
                artboard.style.backgroundImage = `url('${data.bg_image}')`;
                document.getElementById('bgImage').value = data.bg_image;
            } else {
                artboard.style.backgroundImage = 'none';
                document.getElementById('bgImage').value = '';
            }

            document.querySelectorAll('.draggable').forEach(el => el.remove());

            if (data.elements) {
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
                    } else if(elData.type === 'image') {
                        const img = document.createElement('img');
                        img.src = elData.src;
                        img.style.width = elData.width + 'px';
                        el.appendChild(img);
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
        }

        // 4. Lấy dữ liệu trên khung vẽ gom thành Object
        function getCanvasData() {
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
                    data.width = parseInt(window.getComputedStyle(el).width);
                } else if (type === 'avatar') {
                    // [ĐÃ FIX] Lấy width của chính khung (el), thay vì lấy của img bên trong
                    data.width = parseInt(window.getComputedStyle(el).width) || 200;
                    data.borderWidth = parseInt(window.getComputedStyle(el).borderWidth) || 0;
                    data.borderColor = rgbToHex(window.getComputedStyle(el).borderColor);
                }
                elementsData.push(data);
            });

            return {
                bg_color: document.getElementById('bgColor').value,
                bg_image: document.getElementById('bgImage').value,
                elements: elementsData
            };
        }

        // 5. Gửi lên Server để LƯU THÀNH MẪU CHUNG
        function promptSaveTemplate() {
            const name = prompt("Nhập tên cho Mẫu thiết kế này (VD: Mẫu Đại Hội Y Tế):");
            if (!name || name.trim() === '') return;

            const configData = getCanvasData();

            fetch('{{ route('api.save_template') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ name: name.trim(), config: configData })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Đã lưu mẫu thành công vào Thư viện hệ thống!');
                    loadGlobalTemplates(); // Tải lại danh sách cột trái
                }
            })
            .catch(err => {
                alert('Có lỗi xảy ra khi lưu mẫu!');
                console.error(err);
            });
        }

        // Hàm Save Design vào Sự kiện hiện tại (Của bạn trước đó) SỬA LẠI TÍ:
        function saveDesign() {
            const btnSave = document.getElementById('btn-save');
            btnSave.innerHTML = `<span class="material-symbols-outlined animate-spin">refresh</span> Đang lưu...`;

            const configData = getCanvasData();

            fetch('{{ route('api.save_design', $meeting->id) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(configData)
            }).then(res => res.json()).then(data => {
                btnSave.innerHTML = `Đã lưu xong!`; btnSave.classList.replace('bg-indigo-600', 'bg-emerald-500');
                setTimeout(() => { btnSave.innerHTML = `Lưu thiết kế`; btnSave.classList.replace('bg-emerald-500', 'bg-indigo-600'); }, 2000);
            });
        }
    </script>
</body>
</html>