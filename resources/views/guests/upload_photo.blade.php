<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực khuôn mặt - {{ $meeting->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl overflow-hidden">
        <div class="bg-[#5949be] p-6 text-center">
            <h2 class="text-white text-xl font-bold">Xác Thực Khuôn Mặt AI</h2>
            <p class="text-indigo-200 text-sm mt-1">Sự kiện: {{ $meeting->title }}</p>
        </div>

        <div class="p-6">
            <div class="text-center mb-6">
                <p class="text-gray-700 font-medium">Xin chào, <span class="font-bold text-[#5949be]">{{ $guest->full_name }}</span></p>
                <p class="text-sm text-gray-500 mt-2">Vui lòng tải lên hoặc chụp trực tiếp 1 bức ảnh khuôn mặt rõ nét của bạn.</p>
            </div>

            @if(session('success'))
                <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl text-center mb-4 border border-emerald-200">
                    <span class="material-symbols-outlined text-4xl mb-2">check_circle</span>
                    <p class="font-bold">{{ session('success') }}</p>
                    <p class="text-sm mt-1">Bạn có thể đóng trang web này.</p>
                </div>
            @else
                @if(session('error'))
                    <div class="bg-red-50 text-red-700 p-4 rounded-xl text-center mb-4 border border-red-200">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- KHU VỰC HIỂN THỊ CAMERA / PREVIEW ẢNH -->
                <div class="mb-6 relative w-full aspect-[3/4] bg-gray-100 rounded-2xl overflow-hidden border-2 border-dashed border-gray-300 flex items-center justify-center">
                    
                    <!-- Icon mặc định -->
                    <div id="default-ui" class="text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-400">face</span>
                        <p class="text-gray-500 text-sm mt-2">Chưa có ảnh</p>
                    </div>

                    <!-- Khung Video để stream Camera (Mặc định ẩn) -->
                    <video id="camera-stream" class="hidden w-full h-full object-cover transform scale-x-[-1]" autoplay playsinline></video>
                    
                    <!-- Khung xem trước ảnh đã chọn/chụp (Mặc định ẩn) -->
                    <img id="image-preview" class="hidden w-full h-full object-cover" />
                </div>

                <!-- Dùng request()->fullUrl() để giữ lại toàn bộ chữ ký bảo mật khi submit -->
                <form id="photo-form" action="{{ request()->fullUrl() }}" method="POST" enctype="multipart/form-data">                    @csrf
                                    
                    <!-- Nơi chứa file thực tế để gửi lên server (Đã bị ẩn) -->
                    <input type="file" id="photo-input" name="photo" accept="image/jpeg, image/png" class="hidden" required>

                    <!-- CÁC NÚT ĐIỀU KHIỂN -->
                    <div id="action-buttons" class="grid grid-cols-2 gap-3">
                        <!-- Nút Mở Camera trực tiếp -->
                        <button type="button" onclick="startCamera()" class="flex flex-col items-center justify-center p-3 bg-indigo-50 rounded-xl text-[#5949be] hover:bg-indigo-100 transition border border-indigo-100">
                            <span class="material-symbols-outlined mb-1">photo_camera</span>
                            <span class="text-sm font-semibold">Mở Camera</span>
                        </button>

                        <!-- Nút Chọn file từ máy -->
                        <label class="flex flex-col items-center justify-center p-3 bg-indigo-50 rounded-xl text-[#5949be] hover:bg-indigo-100 transition border border-indigo-100 cursor-pointer">
                            <span class="material-symbols-outlined mb-1">upload_file</span>
                            <span class="text-sm font-semibold">Tải ảnh lên</span>
                            <input type="file" accept="image/*" class="hidden" onchange="handleFileUpload(this)">
                        </label>
                    </div>

                    <!-- Nút Chụp Ảnh (Hiện ra khi bật camera) -->
                    <button type="button" id="btn-snap" onclick="snapPhoto()" class="hidden w-full bg-emerald-500 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-emerald-600 transition flex items-center justify-center gap-2 mb-3">
                        <span class="material-symbols-outlined">camera</span>
                        Chụp Ngay
                    </button>

                    <!-- Nút Gửi Ảnh (Hiện ra khi đã có ảnh) -->
                    <button type="submit" id="btn-submit" class="hidden w-full mt-6 bg-[#5949be] text-white font-bold py-4 rounded-xl shadow-lg hover:opacity-90 transition flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">cloud_upload</span>
                        Gửi ảnh lên hệ thống
                    </button>
                </form>

                <!-- Canvas dùng để xử lý ảnh chụp từ Video (Ẩn hoàn toàn khỏi UI) -->
                <canvas id="snap-canvas" class="hidden"></canvas>

            @endif
        </div>
    </div>

    <script>
        const video = document.getElementById('camera-stream');
        const canvas = document.getElementById('snap-canvas');
        const preview = document.getElementById('image-preview');
        const defaultUi = document.getElementById('default-ui');
        const fileInput = document.getElementById('photo-input');
        
        const actionButtons = document.getElementById('action-buttons');
        const btnSnap = document.getElementById('btn-snap');
        const btnSubmit = document.getElementById('btn-submit');

        let stream = null;

        // Xử lý khi người dùng chọn tải file từ máy
        function handleFileUpload(input) {
            if (input.files && input.files[0]) {
                stopCamera(); // Tắt camera nếu đang bật
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    showPreview(e.target.result);
                };
                reader.readAsDataURL(input.files[0]);

                // Đẩy file vào input ẩn để form submit
                fileInput.files = input.files;
                showSubmitButton();
            }
        }

        // Bật Camera WebRTC
        async function startCamera() {
            try {
                // Yêu cầu quyền truy cập Camera trước (facingMode: user)
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: false });
                video.srcObject = stream;
                
                // Điều chỉnh UI
                defaultUi.classList.add('hidden');
                preview.classList.add('hidden');
                video.classList.remove('hidden');
                
                actionButtons.classList.add('hidden');
                btnSubmit.classList.add('hidden');
                btnSnap.classList.remove('hidden');

            } catch (err) {
                alert("Không thể truy cập Camera. Vui lòng kiểm tra quyền hoặc tải ảnh lên từ máy.");
                console.error(err);
            }
        }

        // Tắt Camera
        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                video.classList.add('hidden');
            }
        }

        // Chụp ảnh từ Video Stream
        function snapPhoto() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            
            // Xoay ảnh lại (vì camera trước thường bị lật ngược)
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Chuyển Canvas thành file dạng Base64
            const dataUrl = canvas.toDataURL('image/jpeg');
            showPreview(dataUrl);

            // Tắt Camera
            stopCamera();

            // Chuyển DataURL thành File Object và đẩy vào input ẩn
            canvas.toBlob((blob) => {
                const file = new File([blob], "webcam_snap.jpg", { type: "image/jpeg" });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
            }, 'image/jpeg', 0.9);

            showSubmitButton();
        }

        // Hiển thị ảnh xem trước
        function showPreview(src) {
            preview.src = src;
            preview.classList.remove('hidden');
            defaultUi.classList.add('hidden');
        }

        // Hiện nút Submit và Nút điều khiển
        function showSubmitButton() {
            btnSnap.classList.add('hidden');
            actionButtons.classList.remove('hidden'); // Mở lại menu để họ chọn lại nếu muốn
            btnSubmit.classList.remove('hidden');
        }
    </script>
</body>
</html>