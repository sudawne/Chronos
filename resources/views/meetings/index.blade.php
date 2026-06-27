@extends('layouts.app')

@section('title', 'Quản Lý Cuộc Họp | CHRONOS AI')

@section('content')
<div class="px-4 lg:px-8 pb-12 space-y-8">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-gradient-to-br from-[#5949be]/5 to-transparent rounded-full pointer-events-none"></div>

        <div>
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight mb-1">Danh sách cuộc họp</h1>
            <p class="text-gray-500 font-medium text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">folder_managed</span>
                Quản lý kho lưu trữ và thông tin khách mời
            </p>
        </div>

        <a href="{{ route('meetings.create') }}" class="px-6 py-3 bg-[#5949be] hover:bg-[#4a3ca3] text-white rounded-xl font-bold flex items-center gap-2 transition-all shadow-[0_4px_12px_rgba(89,73,190,0.25)] hover:shadow-[0_6px_16px_rgba(89,73,190,0.35)] hover:-translate-y-0.5 shrink-0 z-10">
            <span class="material-symbols-outlined text-[20px]">add_circle</span>
            Tạo sự kiện mới
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-2xl text-emerald-800 font-bold text-sm flex items-center gap-3 shadow-sm animate-fade-in-down">
            <span class="material-symbols-outlined text-emerald-500 text-[24px]">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <button class="filter-btn active px-5 py-2.5 bg-[#5949be] text-white font-bold text-sm rounded-xl shadow-md transition-all whitespace-nowrap" data-filter="all">
            Tất cả ({{ $meetings->count() }})
        </button>
        <button class="filter-btn px-5 py-2.5 bg-white text-gray-600 hover:bg-gray-50 font-bold text-sm rounded-xl border border-gray-100 transition-all whitespace-nowrap flex items-center gap-2" data-filter="ongoing">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Đang diễn ra
        </button>
        <button class="filter-btn px-5 py-2.5 bg-white text-gray-600 hover:bg-gray-50 font-bold text-sm rounded-xl border border-gray-100 transition-all whitespace-nowrap" data-filter="upcoming">
            Sắp diễn ra
        </button>
        <button class="filter-btn px-5 py-2.5 bg-white text-gray-600 hover:bg-gray-50 font-bold text-sm rounded-xl border border-gray-100 transition-all whitespace-nowrap" data-filter="ended">
            Đã kết thúc
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="meetings-grid">
        
        @forelse($meetings as $meeting)
            @php
                $tz = 'Asia/Ho_Chi_Minh';
                $now = \Carbon\Carbon::now($tz);
                $start = \Carbon\Carbon::parse($meeting->start_time, $tz);
                $end = \Carbon\Carbon::parse($meeting->end_time, $tz);
                
                if ($now->between($start, $end)) {
                    $status = 'ongoing';
                    $statusText = 'Đang diễn ra';
                    $cardClass = 'border-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.15)] ring-1 ring-emerald-400';
                    $badgeClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                    $iconPulse = '<span class="absolute -top-1 -right-1 flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span></span>';
                } elseif ($now->lt($start)) {
                    $status = 'upcoming';
                    $statusText = 'Sắp diễn ra';
                    $cardClass = 'border-gray-100 hover:border-[#5949be]/40 hover:shadow-lg';
                    $badgeClass = 'bg-[#f3f6fd] text-[#5949be] border-indigo-100';
                    $iconPulse = '';
                } else {
                    $status = 'ended';
                    $statusText = 'Đã kết thúc';
                    $cardClass = 'border-gray-100 bg-gray-50/50 opacity-80 hover:opacity-100 grayscale-[0.2]';
                    $badgeClass = 'bg-gray-200 text-gray-600 border-gray-300';
                    $iconPulse = '';
                }
            @endphp

            <div class="meeting-card {{ $status }} bg-white rounded-[2rem] shadow-sm p-6 flex flex-col justify-between transition-all duration-300 relative {{ $cardClass }}" 
                 data-status="{{ $status }}" 
                 data-start="{{ $start->toIso8601String() }}" 
                 data-end="{{ $end->toIso8601String() }}">
                
                <div>
                    <div class="flex justify-between items-center mb-5">
                        <span class="px-3 py-1 bg-white border border-gray-100 text-gray-600 font-bold text-xs rounded-lg flex items-center gap-1.5 shadow-sm">
                            <span class="material-symbols-outlined text-[14px] text-gray-400">group</span>
                            {{ $meeting->guests_count ?? $meeting->guests->count() }} khách
                        </span>
                        
                        <div class="relative pulse-container">
                            <span class="status-badge px-3 py-1 font-bold text-[11px] uppercase tracking-wider rounded-lg border {{ $badgeClass }}">
                                {{ $statusText }}
                            </span>
                            <div class="pulse-icon">{!! $iconPulse !!}</div>
                        </div>
                    </div>

                    <h4 class="font-black text-xl text-gray-800 mb-4 line-clamp-2 leading-tight" title="{{ $meeting->title }}">
                        {{ $meeting->title }}
                    </h4>

                    <div class="space-y-2.5 text-sm font-medium text-gray-500 mb-6">
                        <div class="flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-gray-400 text-[20px]">location_on</span>
                            <span class="text-gray-700 truncate pt-0.5">{{ $meeting->location }}</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-gray-400 text-[20px]">play_circle</span>
                            <span class="pt-0.5">Bắt đầu: <strong class="text-gray-700">{{ $start->format('H:i - d/m/Y') }}</strong></span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <span class="material-symbols-outlined text-gray-400 text-[20px]">stop_circle</span>
                            <span class="pt-0.5">Kết thúc: <strong class="text-gray-700">{{ $end->format('H:i - d/m/Y') }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-4 border-t border-gray-100 mt-auto">
                    <a href="{{ route('meetings.show', $meeting->id) }}" class="flex-1 py-2.5 bg-[#f3f6fd] hover:bg-[#5949be] text-[#5949be] hover:text-white font-bold text-sm rounded-xl text-center transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">settings_input_component</span> Bảng điều khiển
                    </a>
                    
                    <a href="{{ route('meetings.edit', $meeting->id) }}" class="p-2.5 bg-gray-50 text-blue-600 hover:bg-blue-100 rounded-xl transition-colors" title="Chỉnh sửa">
                        <span class="material-symbols-outlined text-[20px]">edit</span>
                    </a>

                    <form action="{{ route('meetings.destroy', $meeting->id) }}" method="POST" class="inline m-0" 
                        onsubmit="confirmAction(event, 'Xóa Sự kiện này?', 'Mọi dữ liệu khuôn mặt và khách mời liên quan sẽ bị xóa vĩnh viễn.')">                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2.5 bg-gray-50 text-red-500 hover:bg-red-100 rounded-xl transition-colors flex items-center justify-center" title="Xóa">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white text-center py-16 rounded-[2rem] border border-gray-100 flex flex-col items-center justify-center">
                <div class="w-24 h-24 bg-[#f3f6fd] rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-[48px] text-[#5949be]/50">calendar_add_on</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Chưa có sự kiện nào</h3>
                <p class="text-gray-500 mb-6">Hãy tạo một cuộc họp mới để bắt đầu sử dụng hệ thống điểm danh AI.</p>
                <a href="{{ route('meetings.create') }}" class="px-6 py-2.5 bg-[#5949be] text-white rounded-xl font-bold flex items-center gap-2 shadow-md">
                    Tạo ngay
                </a>
            </div>
        @endforelse
    </div>
</div>

<script>
    // 1. Logic lọc (Filter)
    function applyFilter(filterValue) {
        const cards = document.querySelectorAll('.meeting-card');
        
        cards.forEach(card => {
            // Xóa bộ đếm cũ nếu có để tránh kẹt hiệu ứng khi người dùng bấm liên tục
            if (card.hideTimeout) clearTimeout(card.hideTimeout);
            if (card.showTimeout) clearTimeout(card.showTimeout);

            if (filterValue === 'all' || card.getAttribute('data-status') === filterValue) {
                // Hiển thị lại card
                card.style.display = 'flex';
                // Delay 20ms để CSS nhận dạng display:flex trước khi kích hoạt hiệu ứng mờ
                card.showTimeout = setTimeout(() => { 
                    card.style.opacity = '1'; 
                    card.style.transform = 'scale(1)'; 
                }, 20);
            } else {
                // Ẩn card đi
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                card.hideTimeout = setTimeout(() => { 
                    card.style.display = 'none'; 
                }, 300); // 300ms bằng thời gian transition CSS
            }
        });
    }

    function initFilters() {
        const btns = document.querySelectorAll('.filter-btn');
        
        btns.forEach(btn => {
            // Xóa bỏ tất cả event listener cũ (Chống kẹt khi trang load lại cục bộ)
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
        });

        // Gắn lại sự kiện click cho các nút mới
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault(); // Ngăn hành vi nhảy trang mặc định
                
                // 1. Reset CSS màu sắc của TẤT CẢ các nút về màu xám
                document.querySelectorAll('.filter-btn').forEach(b => {
                    b.classList.remove('bg-[#5949be]', 'text-white', 'shadow-md', 'active');
                    b.classList.add('bg-white', 'text-gray-600');
                });
                
                // 2. Tô màu nút vừa được bấm
                this.classList.remove('bg-white', 'text-gray-600');
                this.classList.add('bg-[#5949be]', 'text-white', 'shadow-md', 'active');

                // 3. Thực thi logic ẩn/hiện sự kiện
                applyFilter(this.getAttribute('data-filter'));
            });
        });
    }

    // 2. Logic Cập nhật thời gian thực (Real-time Updater)
    function startRealTimeUpdates() {
        if (window.meetingStatusInterval) clearInterval(window.meetingStatusInterval);

        function updateStatuses() {
            const now = new Date();
            const cards = document.querySelectorAll('.meeting-card');
            let hasChanges = false;

            cards.forEach(card => {
                const startTime = new Date(card.getAttribute('data-start'));
                const endTime = new Date(card.getAttribute('data-end'));
                const currentStatus = card.getAttribute('data-status');
                let newStatus = '';

                if (now >= startTime && now <= endTime) {
                    newStatus = 'ongoing';
                } else if (now < startTime) {
                    newStatus = 'upcoming';
                } else {
                    newStatus = 'ended';
                }

                // Nếu thời gian thay đổi làm đổi trạng thái
                if (currentStatus !== newStatus) {
                    hasChanges = true;
                    card.setAttribute('data-status', newStatus);
                    updateCardVisuals(card, newStatus);
                }
            });

            // Nếu trạng thái đổi, áp dụng lại đúng cái bộ lọc đang bấm
            if (hasChanges) {
                const activeFilterBtn = document.querySelector('.filter-btn.active');
                if (activeFilterBtn) applyFilter(activeFilterBtn.getAttribute('data-filter'));
            }
        }

        // Cập nhật ngầm mỗi 10 giây
        window.meetingStatusInterval = setInterval(updateStatuses, 10000);
    }

    function updateCardVisuals(card, status) {
        const badge = card.querySelector('.status-badge');
        const pulse = card.querySelector('.pulse-icon');

        card.classList.remove(
            'border-emerald-400', 'shadow-[0_0_15px_rgba(16,185,129,0.15)]', 'ring-1', 'ring-emerald-400',
            'hover:border-[#5949be]/40', 'hover:shadow-lg',
            'bg-gray-50/50', 'opacity-80', 'hover:opacity-100', 'grayscale-[0.2]'
        );
        badge.className = 'status-badge px-3 py-1 font-bold text-[11px] uppercase tracking-wider rounded-lg border';

        if (status === 'ongoing') {
            card.classList.add('border-emerald-400', 'shadow-[0_0_15px_rgba(16,185,129,0.15)]', 'ring-1', 'ring-emerald-400');
            badge.classList.add('bg-emerald-100', 'text-emerald-700', 'border-emerald-200');
            badge.innerText = 'Đang diễn ra';
            pulse.innerHTML = '<span class="absolute -top-1 -right-1 flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span></span>';
        } else if (status === 'upcoming') {
            card.classList.add('hover:border-[#5949be]/40', 'hover:shadow-lg');
            badge.classList.add('bg-[#f3f6fd]', 'text-[#5949be]', 'border-indigo-100');
            badge.innerText = 'Sắp diễn ra';
            pulse.innerHTML = '';
        } else {
            card.classList.add('bg-gray-50/50', 'opacity-80', 'hover:opacity-100', 'grayscale-[0.2]');
            badge.classList.add('bg-gray-200', 'text-gray-600', 'border-gray-300');
            badge.innerText = 'Đã kết thúc';
            pulse.innerHTML = '';
        }
    }

    // Bọc hàm khởi tạo để gọi mọi lúc
    function initAll() {
        initFilters();
        startRealTimeUpdates();
    }

    // Kiểm tra DOM xem đã render xong chưa (Tránh lỗi script chạy quá sớm hoặc quá trễ)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    // Hỗ trợ thư viện Swup (nếu có dùng để chuyển trang mượt)
    if (typeof swup !== 'undefined') {
        swup.hooks.on('page:view', initAll);
    }
</script>
@endsection