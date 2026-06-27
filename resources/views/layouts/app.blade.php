<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'CHRONOS - AI Meeting Dashboard')</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://unpkg.com/swup@4"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.4s ease, color 0.4s ease; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        
        body { background-color: #f3f6fd; color: #2b2f34; }
        /* Nền tối sâu (Deep Space) */
        html.dark body { background-color: #0B1120; color: #f8fafc; } 
        
        @media (min-width: 768px) {
            #sidebar { width: 6rem; }
            #main-wrapper { margin-left: 8rem; }
            
            #sidebar.is-expanded { width: 16rem; }
            #sidebar.is-expanded ~ #main-wrapper { margin-left: 18rem; }
        }

        #sidebar, #main-wrapper {
            transition: width 0.4s cubic-bezier(0.16, 1, 0.3, 1), margin-left 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .transition-fade {
            transition: opacity 0.4s ease, transform 0.4s ease;
            opacity: 1; transform: translateY(0);
        }
        html.is-animating .transition-fade {
            opacity: 0; transform: translateY(20px);
        }

        @keyframes wave {
            0%, 60%, 100% { transform: rotate(0deg); }
            10%, 30% { transform: rotate(14deg); }
            20% { transform: rotate(-8deg); }
            40% { transform: rotate(-4deg); }
            50% { transform: rotate(10deg); }
        }
        .animate-waving-hand { animation: wave 2.5s infinite; transform-origin: 70% 70%; display: inline-block; }
    </style>
</head>
<body class="min-h-screen relative overflow-x-hidden dark:bg-[#0B1120]">

    <div class="fixed inset-0 z-[-1] hidden dark:block bg-[radial-gradient(ellipse_80%_50%_at_50%_-20%,rgba(89,73,190,0.25),rgba(255,255,255,0))] pointer-events-none"></div>

    <div id="mobile-overlay" class="fixed inset-0 bg-[#2b2f34]/40 backdrop-blur-sm z-[50] hidden opacity-0 transition-opacity duration-300 md:hidden"></div>

    <aside id="sidebar" class="group/sidebar absolute md:fixed left-0 top-0 z-[60] h-[calc(100vh-2.5rem)] w-64 md:w-auto -translate-x-[120%] md:translate-x-0 m-5 rounded-[3rem] flex flex-col justify-between py-10 bg-gradient-to-b from-[#5949be] to-[#6C5DD3] dark:from-[#13182B] dark:to-[#0B1120] shadow-[0px_20px_40px_rgba(89,73,190,0.12)] dark:shadow-none border dark:border-white/5">
        
        <script>
            if (localStorage.getItem('sidebar_expanded') === 'true') {
                document.getElementById('sidebar').classList.add('is-expanded');
            }
        </script>

        <button id="desktop-toggle" class="hidden md:flex absolute -right-4 top-16 w-8 h-8 bg-white dark:bg-[#1A2235] rounded-full shadow-lg items-center justify-center text-[#5949be] dark:text-indigo-400 hover:scale-110 transition-transform border border-indigo-100 dark:border-white/10 z-[70]">
            <span class="material-symbols-outlined text-[20px] transition-transform duration-300 group-[.is-expanded]/sidebar:rotate-180">chevron_right</span>
        </button>
        
        <div class="text-white text-xl font-black mb-8 px-5 flex items-center justify-start md:justify-center group-[.is-expanded]/sidebar:md:justify-start w-full transition-all duration-300">
            <a href="{{ url('/') }}" class="navbar-brand flex items-center gap-0">
                <img src="{{ asset('images/logo_tach.png') }}" alt="Logo Dự Án" class="h-10 w-auto brightness-0 invert drop-shadow-md flex-shrink-0">
                <span class="text-xl font-black tracking-wider whitespace-nowrap overflow-hidden transition-all duration-300 opacity-100 max-w-[150px] ml-3 md:ml-0 md:opacity-0 md:max-w-0 group-[.is-expanded]/sidebar:md:ml-3 group-[.is-expanded]/sidebar:md:opacity-100 group-[.is-expanded]/sidebar:md:max-w-[150px]">
                    CHRONOS
                </span>
            </a>
        </div>
        
        <nav id="menu" class="flex flex-col w-full space-y-2 mt-4">   
            
            {{-- Đã thêm 1px spread vào shadow (0_1px_#màu) để trám kín lỗi vệt trắng khi render màn hình --}}
            <a href="{{ route('dashboard') }}" 
            class="{{ request()->routeIs('dashboard') 
                ? 'relative bg-[#f3f6fd] dark:bg-[#0B1120] text-[#5949be] dark:text-indigo-400 rounded-l-[1.5rem] ml-4 py-4 flex items-center pl-7 before:absolute before:top-[-20px] before:right-0 before:w-5 before:h-5 before:rounded-br-[20px] before:shadow-[5px_5px_0_1px_#f3f6fd] dark:before:shadow-[5px_5px_0_1px_#0B1120] after:absolute after:bottom-[-20px] after:right-0 after:w-5 after:h-5 after:rounded-tr-[20px] after:shadow-[5px_-5px_0_1px_#f3f6fd] dark:after:shadow-[5px_-5px_0_1px_#0B1120] z-10' 
                : 'flex items-center pl-7 text-indigo-100/70 dark:text-slate-400 py-4 ml-4 transition-all duration-300 hover:text-white dark:hover:text-indigo-300 hover:translate-x-2' }}">
                <span class="material-symbols-outlined flex-shrink-0" style="{{ request()->routeIs('dashboard') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">dashboard</span>
                <span class="font-semibold text-sm whitespace-nowrap overflow-hidden transition-all duration-300 opacity-100 max-w-[150px] ml-4 md:ml-0 md:opacity-0 md:max-w-0 group-[.is-expanded]/sidebar:md:ml-4 group-[.is-expanded]/sidebar:md:opacity-100 group-[.is-expanded]/sidebar:md:max-w-[150px]">Tổng quan</span>
            </a>

            @can('meeting.create')
            <a href="{{ route('meetings.create') }}" 
            class="{{ request()->routeIs('meetings.create') 
                ? 'relative bg-[#f3f6fd] dark:bg-[#0B1120] text-[#5949be] dark:text-indigo-400 rounded-l-[1.5rem] ml-4 py-4 flex items-center pl-7 before:absolute before:top-[-20px] before:right-0 before:w-5 before:h-5 before:rounded-br-[20px] before:shadow-[5px_5px_0_1px_#f3f6fd] dark:before:shadow-[5px_5px_0_1px_#0B1120] after:absolute after:bottom-[-20px] after:right-0 after:w-5 after:h-5 after:rounded-tr-[20px] after:shadow-[5px_-5px_0_1px_#f3f6fd] dark:after:shadow-[5px_-5px_0_1px_#0B1120] z-10' 
                : 'flex items-center pl-7 text-indigo-100/70 dark:text-slate-400 py-4 ml-4 transition-all duration-300 hover:text-white dark:hover:text-indigo-300 hover:translate-x-2' }}">
                <span class="material-symbols-outlined flex-shrink-0" style="{{ request()->routeIs('meetings.create') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">add_circle</span>
                <span class="font-semibold text-sm whitespace-nowrap overflow-hidden transition-all duration-300 opacity-100 max-w-[150px] ml-4 md:ml-0 md:opacity-0 md:max-w-0 group-[.is-expanded]/sidebar:md:ml-4 group-[.is-expanded]/sidebar:md:opacity-100 group-[.is-expanded]/sidebar:md:max-w-[150px]">Tạo cuộc họp</span>
            </a>
            @endcan

            <a href="{{ route('meetings.index') }}" 
            class="{{ request()->routeIs('meetings.index', 'meetings.show', 'meetings.edit') 
                ? 'relative bg-[#f3f6fd] dark:bg-[#0B1120] text-[#5949be] dark:text-indigo-400 rounded-l-[1.5rem] ml-4 py-4 flex items-center pl-7 before:absolute before:top-[-20px] before:right-0 before:w-5 before:h-5 before:rounded-br-[20px] before:shadow-[5px_5px_0_1px_#f3f6fd] dark:before:shadow-[5px_5px_0_1px_#0B1120] after:absolute after:bottom-[-20px] after:right-0 after:w-5 after:h-5 after:rounded-tr-[20px] after:shadow-[5px_-5px_0_1px_#f3f6fd] dark:after:shadow-[5px_-5px_0_1px_#0B1120] z-10' 
                : 'flex items-center pl-7 text-indigo-100/70 dark:text-slate-400 py-4 ml-4 transition-all duration-300 hover:text-white dark:hover:text-indigo-300 hover:translate-x-2' }}">
                <span class="material-symbols-outlined flex-shrink-0" style="{{ request()->routeIs('meetings.index', 'meetings.show', 'meetings.edit') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">view_list</span>
                <span class="font-semibold text-sm whitespace-nowrap overflow-hidden transition-all duration-300 opacity-100 max-w-[150px] ml-4 md:ml-0 md:opacity-0 md:max-w-0 group-[.is-expanded]/sidebar:md:ml-4 group-[.is-expanded]/sidebar:md:opacity-100 group-[.is-expanded]/sidebar:md:max-w-[150px]">Danh sách</span>
            </a>

            @role('Admin')
            <div class="mt-6 mb-2 ml-8 text-[10px] font-black uppercase tracking-[0.2em] text-indigo-300/60 dark:text-indigo-500/50">Quản trị</div>
            
            <a href="{{ route('admin.users.index') }}" 
            class="{{ request()->routeIs('admin.users.*', 'admin.matrix.*') 
                ? 'relative bg-[#f3f6fd] dark:bg-[#0B1120] text-[#5949be] dark:text-indigo-400 rounded-l-[1.5rem] ml-4 py-4 flex items-center pl-7 before:absolute before:top-[-20px] before:right-0 before:w-5 before:h-5 before:rounded-br-[20px] before:shadow-[5px_5px_0_1px_#f3f6fd] dark:before:shadow-[5px_5px_0_1px_#0B1120] after:absolute after:bottom-[-20px] after:right-0 after:w-5 after:h-5 after:rounded-tr-[20px] after:shadow-[5px_-5px_0_1px_#f3f6fd] dark:after:shadow-[5px_-5px_0_1px_#0B1120] z-10' 
                : 'flex items-center pl-7 text-indigo-100/70 dark:text-slate-400 py-4 ml-4 transition-all duration-300 hover:text-white dark:hover:text-indigo-300 hover:translate-x-2' }}">
                <span class="material-symbols-outlined flex-shrink-0" style="{{ request()->routeIs('admin.users.*', 'admin.matrix.*') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">admin_panel_settings</span>
                <span class="font-semibold text-sm whitespace-nowrap overflow-hidden transition-all duration-300 opacity-100 max-w-[150px] ml-4 md:ml-0 md:opacity-0 md:max-w-0 group-[.is-expanded]/sidebar:md:ml-4 group-[.is-expanded]/sidebar:md:opacity-100 group-[.is-expanded]/sidebar:md:max-w-[150px]">Phân quyền</span>
            </a>
            @endrole
        </nav>

        <div class="flex flex-col w-full space-y-4">
            <button onclick="toggleSettingsPanel()" class="w-full flex items-center text-indigo-100/70 dark:text-slate-400 py-3 px-5 mx-4 rounded-2xl transition-all duration-300 hover:bg-white/10 dark:hover:text-indigo-300">
                <span class="material-symbols-outlined flex-shrink-0">settings</span>
                <span class="font-semibold text-sm whitespace-nowrap overflow-hidden transition-all duration-300 opacity-100 max-w-[150px] ml-4 md:ml-0 md:opacity-0 md:max-w-0 group-[.is-expanded]/sidebar:md:ml-4 group-[.is-expanded]/sidebar:md:opacity-100 group-[.is-expanded]/sidebar:md:max-w-[150px]">Cài đặt</span>
            </button>
            
            <a href="{{ route('profile.edit') }}" class="px-5 pb-2 w-full flex items-center justify-start md:justify-center group-[.is-expanded]/sidebar:md:justify-start transition-all duration-300 hover:opacity-80">
                <div class="w-12 h-12 rounded-full border-2 border-indigo-200/50 dark:border-indigo-500/30 overflow-hidden shadow-lg bg-white dark:bg-slate-800 flex-shrink-0">
                    <img class="w-full h-full object-cover" 
                         src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name ?? 'GV').'&color=5949be&background=e4e8f1' }}" 
                         onerror="this.src='https://ui-avatars.com/api/?name=Admin&color=5949be&background=e4e8f1'"/>
                </div>
                <div class="flex flex-col justify-center whitespace-nowrap overflow-hidden transition-all duration-300 opacity-100 max-w-[150px] ml-3 md:ml-0 md:opacity-0 md:max-w-0 group-[.is-expanded]/sidebar:md:ml-3 group-[.is-expanded]/sidebar:md:opacity-100 group-[.is-expanded]/sidebar:md:max-w-[150px]">
                    <span class="text-white font-bold text-sm leading-tight">{{ Auth::user()->name ?? 'Thầy/Cô' }}</span>
                    <span class="text-indigo-200 dark:text-slate-400 text-[11px]">Giáo viên</span>
                </div>
            </a>
        </div>
    </aside>

    <main id="main-wrapper" class="ml-0 px-5 md:px-0 md:mr-8 pt-6 md:pt-8 pb-20">
        <header class="flex justify-between items-center w-full h-20 mb-8 bg-transparent">
            <div class="flex items-center gap-4">
                <button id="mobile-toggle" class="md:hidden p-2.5 text-gray-700 dark:text-slate-300 bg-white dark:bg-white/5 rounded-xl shadow-sm border border-transparent dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined">menu</span>
                </button>

                <div class="hidden sm:flex flex-col justify-center mt-1">
                    <h2 class="text-[22px] font-extrabold text-gray-800 dark:text-white tracking-tight flex items-center gap-2">
                        Xin chào, {{ Auth::user()->name ?? 'Thầy/Cô' }} <span class="text-2xl animate-waving-hand">👋</span>
                    </h2>
                    <p class="text-[13px] text-gray-500 dark:text-slate-400 font-medium flex items-center gap-1.5 mt-0.5">
                        <span class="material-symbols-outlined text-[15px] text-[#5949be] dark:text-indigo-400">calendar_today</span>
                        <span id="chronos-clock">Đang đồng bộ thời gian...</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 md:gap-5">
                <div class="relative hidden lg:block group cursor-pointer" onclick="openSearchModal()">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-400 text-[22px] group-hover:text-indigo-500 transition-colors">search</span>
                    <div class="pl-12 pr-16 py-2.5 w-64 xl:w-80 bg-white dark:bg-[#151A2D] rounded-2xl border border-gray-100 dark:border-white/5 shadow-sm text-[14px] text-gray-400 dark:text-slate-400 select-none flex items-center group-hover:border-indigo-500/50 transition-colors">
                        Tìm kiếm sự kiện...
                    </div>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 pointer-events-none">
                        <kbd class="px-1.5 py-1 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-md text-[10px] font-bold text-gray-400 dark:text-slate-400">Ctrl</kbd>
                        <kbd class="px-1.5 py-1 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-md text-[10px] font-bold text-gray-400 dark:text-slate-400">K</kbd>
                    </div>
                </div>

                <div class="hidden sm:block w-px h-8 bg-gray-200/80 dark:bg-white/10 mx-1"></div>
                <div class="flex items-center gap-2.5">
                    <button onclick="toggleSettingsPanel()" title="Cài đặt hệ thống" class="p-2.5 text-gray-500 dark:text-slate-300 bg-white dark:bg-[#151A2D] hover:bg-indigo-50 dark:hover:bg-indigo-500/20 hover:text-[#5949be] dark:hover:text-indigo-400 rounded-full shadow-sm border border-gray-100 dark:border-white/5 transition-colors flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]">tune</span>
                    </button>
                    <button class="p-2.5 text-gray-500 dark:text-slate-300 bg-white dark:bg-[#151A2D] hover:bg-indigo-50 dark:hover:bg-indigo-500/20 hover:text-[#5949be] dark:hover:text-indigo-400 rounded-full shadow-sm border border-gray-100 dark:border-white/5 transition-colors relative flex items-center justify-center">
                        <span class="material-symbols-outlined text-[22px]">notifications</span>
                        <span class="absolute top-2 right-2.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white dark:border-[#151A2D]"></span>
                    </button>
                </div>
            </div>
        </header>

        <div id="swup" class="transition-fade">
            @yield('content')
        </div>
    </main>

    <div id="search-modal-backdrop" onclick="closeSearchModal()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[150] hidden opacity-0 transition-opacity duration-300 items-start justify-center pt-20">
        <div id="search-modal-box" onclick="event.stopPropagation()" class="bg-white dark:bg-[#151A2D] rounded-2xl w-full max-w-xl mx-4 shadow-2xl border border-slate-100 dark:border-white/10 overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[500px]">
            <div class="p-4 border-b border-slate-100 dark:border-white/5 flex items-center gap-3 relative">
                <span class="material-symbols-outlined text-gray-400 dark:text-slate-400 text-[24px]">search</span>
                <input type="text" id="global-search-input" autocomplete="off" placeholder="Nhập tên cuộc họp để tìm kiếm..." class="w-full bg-transparent border-none text-slate-800 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-500 focus:ring-0 p-0 text-base outline-none">
                <button onclick="closeSearchModal()" class="text-xs bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-300 px-2 py-1 rounded-md hover:bg-slate-200 dark:hover:bg-white/10 transition-colors">ESC</button>
            </div>
            <div id="search-results-box" class="flex-1 overflow-y-auto p-2 space-y-1 divide-y divide-slate-50 dark:divide-white/5">
                <div class="text-slate-400 dark:text-slate-500 text-sm text-center py-8">Gõ từ khóa để bắt đầu tra cứu dữ liệu...</div>
            </div>
        </div>
    </div>

    <div id="settings-backdrop" onclick="toggleSettingsPanel()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[90] hidden opacity-0 transition-opacity duration-300"></div>
    <div id="settings-panel" class="fixed top-0 right-0 w-80 max-w-full h-screen bg-white dark:bg-[#0B1120] z-[100] transform translate-x-full transition-transform duration-300 ease-in-out shadow-2xl border-l border-slate-100 dark:border-white/5 flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-white/5">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400">tune</span> Tùy chỉnh UI
            </h3>
            <button onclick="toggleSettingsPanel()" class="text-slate-400 hover:text-rose-500 transition-colors bg-slate-100 dark:bg-white/5 hover:bg-rose-50 dark:hover:bg-rose-500/20 w-8 h-8 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <div class="p-6 flex-1 overflow-y-auto space-y-8">
            <div>
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3 uppercase tracking-wider">Chủ đề giao diện</h4>
                <div class="bg-slate-50 dark:bg-[#151A2D] p-1.5 rounded-xl flex gap-1 border border-slate-200 dark:border-white/5">
                    <button onclick="setThemeOption('light')" id="btn-theme-light" class="flex-1 flex flex-col items-center gap-2 py-3 rounded-lg text-sm font-semibold transition-all">
                        <span class="material-symbols-outlined">light_mode</span> Sáng
                    </button>
                    <button onclick="setThemeOption('dark')" id="btn-theme-dark" class="flex-1 flex flex-col items-center gap-2 py-3 rounded-lg text-sm font-semibold transition-all">
                        <span class="material-symbols-outlined">dark_mode</span> Tối
                    </button>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3 uppercase tracking-wider">Sidebar mặc định</h4>
                <div class="bg-slate-50 dark:bg-[#151A2D] p-1.5 rounded-xl flex gap-1 border border-slate-200 dark:border-white/5">
                    <button onclick="setSidebarOption('collapsed')" id="btn-sidebar-collapsed" class="flex-1 flex flex-col items-center gap-2 py-3 rounded-lg text-sm font-semibold transition-all">
                        <span class="material-symbols-outlined">vertical_split</span> Thu gọn
                    </button>
                    <button onclick="setSidebarOption('expanded')" id="btn-sidebar-expanded" class="flex-1 flex flex-col items-center gap-2 py-3 rounded-lg text-sm font-semibold transition-all">
                        <span class="material-symbols-outlined">view_sidebar</span> Mở rộng
                    </button>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-transparent">
            <button onclick="toggleSettingsPanel()" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-sm transition-colors">Hoàn tất</button>
        </div>
    </div>

    <script>
        const swup = new Swup({ containers: ['#swup', '#menu'] });

        function initChronosClock() {
            const clockElement = document.getElementById('chronos-clock');
            if (!clockElement) return;
            function updateTime() {
                const now = new Date();
                const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                const timeOptions = { hour: '2-digit', minute: '2-digit' };
                clockElement.innerHTML = `${now.toLocaleDateString('vi-VN', dateOptions)} <span class="mx-1 text-gray-300 dark:text-slate-600">|</span> <span class="font-bold text-[#5949be] dark:text-indigo-400">${now.toLocaleTimeString('vi-VN', timeOptions)}</span>`;
            }
            updateTime(); setInterval(updateTime, 10000); 
        }

        const settingsPanel = document.getElementById('settings-panel');
        const settingsBackdrop = document.getElementById('settings-backdrop');

        function toggleSettingsPanel() {
            const isClosed = settingsPanel.classList.contains('translate-x-full');
            if (isClosed) {
                settingsBackdrop.classList.remove('hidden');
                setTimeout(() => {
                    settingsBackdrop.classList.remove('opacity-0');
                    settingsPanel.classList.remove('translate-x-full');
                }, 10);
                syncSettingsUI();
            } else {
                settingsPanel.classList.add('translate-x-full');
                settingsBackdrop.classList.add('opacity-0');
                setTimeout(() => settingsBackdrop.classList.add('hidden'), 300);
            }
        }

        function setThemeOption(theme) {
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
            syncSettingsUI();
        }

        function setSidebarOption(state) {
            const sidebar = document.getElementById('sidebar');
            if (state === 'expanded') {
                sidebar.classList.add('is-expanded');
                localStorage.setItem('sidebar_expanded', 'true');
            } else {
                sidebar.classList.remove('is-expanded');
                localStorage.setItem('sidebar_expanded', 'false');
            }
            syncSettingsUI();
        }

        function syncSettingsUI() {
            const isDark = document.documentElement.classList.contains('dark');
            const btnDark = document.getElementById('btn-theme-dark');
            const btnLight = document.getElementById('btn-theme-light');
            if (isDark) {
                btnDark.className = "flex-1 flex flex-col items-center gap-2 py-3 rounded-lg text-sm font-bold bg-[#1A2235] text-indigo-400 shadow-sm border border-indigo-500/30 transition-all";
                btnLight.className = "flex-1 flex flex-col items-center gap-2 py-3 rounded-lg text-sm font-medium text-slate-400 hover:text-white transition-all";
            } else {
                btnLight.className = "flex-1 flex flex-col items-center gap-2 py-3 rounded-lg text-sm font-bold bg-white text-indigo-600 shadow-sm border border-slate-200 transition-all";
                btnDark.className = "flex-1 flex flex-col items-center gap-2 py-3 rounded-lg text-sm font-medium text-slate-500 hover:text-slate-700 transition-all";
            }
            
            const isExpanded = document.getElementById('sidebar').classList.contains('is-expanded');
            const btnExpanded = document.getElementById('btn-sidebar-expanded');
            const btnCollapsed = document.getElementById('btn-sidebar-collapsed');
            
            const btnActiveClasses = isDark 
                ? "flex-1 flex flex-col items-center gap-2 py-3 rounded-lg text-sm font-bold bg-[#1A2235] text-indigo-400 shadow-sm border border-indigo-500/30 transition-all"
                : "flex-1 flex flex-col items-center gap-2 py-3 rounded-lg text-sm font-bold bg-white text-indigo-600 shadow-sm border border-slate-200 transition-all";
            const btnInactiveClasses = isDark 
                ? "flex-1 flex flex-col items-center gap-2 py-3 rounded-lg text-sm font-medium text-slate-400 hover:text-white transition-all"
                : "flex-1 flex flex-col items-center gap-2 py-3 rounded-lg text-sm font-medium text-slate-500 hover:text-slate-700 transition-all";

            if (isExpanded) {
                btnExpanded.className = btnActiveClasses;
                btnCollapsed.className = btnInactiveClasses;
            } else {
                btnCollapsed.className = btnActiveClasses;
                btnExpanded.className = btnInactiveClasses;
            }
        }

        const searchModalBackdrop = document.getElementById('search-modal-backdrop');
        const searchModalBox = document.getElementById('search-modal-box');
        const searchInput = document.getElementById('global-search-input');
        const resultsBox = document.getElementById('search-results-box');

        function openSearchModal() {
            searchModalBackdrop.classList.remove('hidden');
            searchModalBackdrop.classList.add('flex');
            setTimeout(() => {
                searchModalBackdrop.classList.remove('opacity-0');
                searchModalBox.classList.remove('scale-95');
                searchModalBox.classList.add('scale-100');
                searchInput.focus();
            }, 10);
        }

        function closeSearchModal() {
            searchModalBox.classList.remove('scale-100');
            searchModalBox.classList.add('scale-95');
            searchModalBackdrop.classList.add('opacity-0');
            setTimeout(() => {
                searchModalBackdrop.classList.add('hidden');
                searchModalBackdrop.classList.remove('flex');
                searchInput.value = '';
                resultsBox.innerHTML = '<div class="text-slate-400 dark:text-slate-500 text-sm text-center py-8">Gõ từ khóa để bắt đầu tra cứu dữ liệu...</div>';
            }, 300);
        }

        window.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault(); openSearchModal();
            }
            if (e.key === 'Escape') closeSearchModal();
        });

        let searchTimeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
                resultsBox.innerHTML = '<div class="text-slate-400 dark:text-slate-500 text-sm text-center py-8">Vui lòng nhập tối thiểu 2 ký tự...</div>';
                return;
            }

            resultsBox.innerHTML = '<div class="text-slate-400 dark:text-slate-500 text-sm text-center py-8 animate-pulse">Đang lục tìm CSDL...</div>';

            searchTimeout = setTimeout(() => {
                fetch(`/api/global-search?query=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        resultsBox.innerHTML = '';
                        if (data.length === 0) {
                            resultsBox.innerHTML = '<div class="text-slate-400 dark:text-slate-500 text-sm text-center py-8">Không tìm thấy cuộc họp nào phù hợp.</div>';
                            return;
                        }
                        data.forEach(item => {
                            const html = `
                                <a href="/meetings/${item.id}" class="flex items-center justify-between p-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-white/5 group transition-all">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-[#0B1120] border dark:border-white/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined">forum</span>
                                        </div>
                                        <div>
                                            <span class="text-sm font-bold text-slate-800 dark:text-white block group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">${item.title}</span>
                                            <span class="text-xs text-slate-400 flex items-center gap-1 mt-0.5"><span class="material-symbols-outlined text-[14px]">location_on</span>${item.location}</span>
                                        </div>
                                    </div>
                                    <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 group-hover:translate-x-1 transition-transform">chevron_right</span>
                                </a>
                            `;
                            resultsBox.insertAdjacentHTML('beforeend', html);
                        });
                    })
                    .catch(() => {
                        resultsBox.innerHTML = '<div class="text-rose-400 text-sm text-center py-8">Lỗi kết nối máy chủ tra cứu!</div>';
                    });
            }, 300);
        });

        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const desktopToggle = document.getElementById('desktop-toggle');
            const mobileToggle = document.getElementById('mobile-toggle');
            const overlay = document.getElementById('mobile-overlay');

            initChronosClock();

            desktopToggle.addEventListener('click', () => {
                sidebar.classList.toggle('is-expanded');
                localStorage.setItem('sidebar_expanded', sidebar.classList.contains('is-expanded'));
                syncSettingsUI();
            });

            function toggleMobileMenu() {
                const isClosed = sidebar.classList.contains('-translate-x-[120%]');
                if (isClosed) {
                    sidebar.classList.replace('-translate-x-[120%]', 'translate-x-0');
                    overlay.classList.remove('hidden');
                    setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                } else {
                    sidebar.classList.replace('translate-x-0', '-translate-x-[120%]');
                    overlay.classList.add('opacity-0');
                    setTimeout(() => overlay.classList.add('hidden'), 300);
                }
            }

            if(mobileToggle) mobileToggle.addEventListener('click', toggleMobileMenu);
            if(overlay) overlay.addEventListener('click', toggleMobileMenu);
        });
    </script>

    //Thông báo
    <!-- THƯ VIỆN SWEETALERT2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- CẤU HÌNH THÔNG BÁO GLOBAL -->
    <script>
        // 1. Cấu hình Toast trượt từ góc phải (Dành cho báo thành công/lỗi)
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // 2. Bắt các Session từ Laravel Controller và bắn Toast
        @if(session('success'))
            Toast.fire({ icon: 'success', title: '{!! session('success') !!}' });
        @endif

        @if(session('error'))
            Toast.fire({ icon: 'error', title: '{!! session('error') !!}' });
        @endif

        @if(session('warning'))
            Toast.fire({ icon: 'warning', title: '{!! session('warning') !!}' });
        @endif

        // 3. Hàm Xác nhận thông minh (Dành cho các nút Xóa / Gửi Mail)
        function confirmAction(event, title, text = 'Hành động này không thể hoàn tác!') {
            event.preventDefault(); // Chặn form submit ngay lập tức
            const form = event.target; 

            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5', // Màu Indigo 600
                cancelButtonColor: '#f43f5e', // Màu Rose 500
                confirmButtonText: 'Xác nhận',
                cancelButtonText: 'Hủy',
                backdrop: `rgba(15, 23, 42, 0.5)` // Làm mờ nền cực đẹp
            }).then((result) => {
                if (result.isConfirmed) {
                    // Hiển thị loading xoay xoay sau khi bấm xác nhận
                    Swal.fire({
                        title: 'Đang xử lý...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    form.submit(); // Cho phép form tiếp tục gửi đi
                }
            });
        }
    </script>
</body>
</html>