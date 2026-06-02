<!-- resources/views/components/global-loader.blade.php -->
<div id="global-loader" class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
    
    <!-- Vòng tròn Spinner (Tailwind) -->
    <svg class="w-14 h-14 text-primary animate-spin mb-4 drop-shadow-lg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
        <path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>

    <!-- Chữ hiển thị -->
    <p id="global-loader-text" class="text-white font-body-lg font-medium tracking-wide drop-shadow-md">
        Đang tải dữ liệu...
    </p>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hàm BẬT loading
        // Bạn có thể truyền text vào, ví dụ: showLoader('Đang lưu thông tin...')
        window.showLoader = function(message = 'Đang tải dữ liệu...') {
            const loader = document.getElementById('global-loader');
            const textEl = document.getElementById('global-loader-text');
            
            if (loader) {
                if (textEl) textEl.innerText = message;
                loader.classList.remove('opacity-0', 'pointer-events-none');
                loader.classList.add('opacity-100', 'pointer-events-auto');
            }
        };

        // Hàm TẮT loading
        window.hideLoader = function() {
            const loader = document.getElementById('global-loader');
            if (loader) {
                loader.classList.remove('opacity-100', 'pointer-events-auto');
                loader.classList.add('opacity-0', 'pointer-events-none');
            }
        };

        window.addEventListener('load', function() {
            setTimeout(hideLoader, 300); // Delay 300ms để hiệu ứng mượt hơn
        });
    });
</script>