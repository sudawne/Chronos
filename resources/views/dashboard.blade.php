@extends('layouts.app')

@section('title', 'Trung tâm Chỉ huy | CHRONOS AI')

@section('content')
<div class="px-4 lg:px-8 pb-12 bg-slate-50/50 min-h-screen space-y-8">

    {{-- HEADER & BỘ LỌC --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">AI Command Center</h1>
            <p class="text-sm font-medium text-slate-500">Giám sát hiệu suất và lưu lượng điểm danh thời gian thực</p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-4">
            <div class="inline-flex bg-white rounded-xl shadow-sm border border-slate-200 p-1 flex-wrap">
                <a href="{{ route('dashboard', ['range' => 'today']) }}" data-no-swup class="px-4 py-2 rounded-lg text-sm transition-colors {{ $range == 'today' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-500 hover:bg-slate-50 font-medium' }}">Hôm nay</a>
                <a href="{{ route('dashboard', ['range' => 'week']) }}" data-no-swup class="px-4 py-2 rounded-lg text-sm transition-colors {{ $range == 'week' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-500 hover:bg-slate-50 font-medium' }}">Tuần</a>
                <a href="{{ route('dashboard', ['range' => 'month']) }}" data-no-swup class="px-4 py-2 rounded-lg text-sm transition-colors {{ $range == 'month' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-500 hover:bg-slate-50 font-medium' }}">Tháng</a>
                <a href="{{ route('dashboard', ['range' => 'year']) }}" data-no-swup class="px-4 py-2 rounded-lg text-sm transition-colors {{ $range == 'year' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-500 hover:bg-slate-50 font-medium' }}">Năm</a>
                <a href="{{ route('dashboard', ['range' => 'all']) }}" data-no-swup class="px-4 py-2 rounded-lg text-sm transition-colors {{ $range == 'all' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-500 hover:bg-slate-50 font-medium' }}">Tất cả</a>
            </div>
            
            <a href="{{ route('meetings.create') }}" data-no-swup class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl shadow-sm hover:bg-indigo-700 transition-all font-semibold text-sm active:scale-95">
                <span class="material-symbols-outlined text-[20px]">add</span> Tạo sự kiện
            </a>
        </div>
    </div>

    {{-- ZONE 1: KPI TỔNG QUAN --}}
    <div>
        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">query_stats</span> Zone 1: Chỉ số cốt lõi
        </h2>
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 xl:gap-6">
            {{-- Tổng sự kiện --}}
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-center">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-[20px]">event_note</span>
                </div>
                <h4 class="text-slate-500 text-xs font-semibold mb-1 uppercase">Sự kiện</h4>
                <h2 class="text-2xl font-black text-slate-800">{{ number_format($totalMeetings) }}</h2>
            </div>

            {{-- Bình quân khách --}}
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-center">
                <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-[20px]">groups</span>
                </div>
                <h4 class="text-slate-500 text-xs font-semibold mb-1 uppercase">Bình quân/Sự kiện</h4>
                <h2 class="text-2xl font-black text-slate-800">{{ number_format($avgGuests) }}</h2>
            </div>

            {{-- Tổng Đại biểu --}}
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-center">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-[20px]">group_add</span>
                </div>
                <h4 class="text-slate-500 text-xs font-semibold mb-1 uppercase">Tổng đại biểu</h4>
                <h2 class="text-2xl font-black text-slate-800">{{ number_format($totalGuests) }}</h2>
            </div>

            {{-- Đã Check-in --}}
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-5 rounded-3xl shadow-md border border-emerald-400 flex flex-col justify-center text-white relative overflow-hidden">
                <div class="absolute -right-4 -top-4 opacity-20"><span class="material-symbols-outlined text-[80px]">how_to_reg</span></div>
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center mb-3 backdrop-blur-sm">
                    <span class="material-symbols-outlined text-[20px] text-white">done_all</span>
                </div>
                <h4 class="text-emerald-50 text-xs font-semibold mb-1 uppercase">Đã Check-in</h4>
                <div class="flex items-end gap-2">
                    <h2 class="text-2xl font-black">{{ number_format($checkedInGuests) }}</h2>
                    <span class="text-sm font-bold bg-white/20 px-2 py-0.5 rounded mb-1">{{ $attendanceRate }}%</span>
                </div>
            </div>

            {{-- Vắng mặt --}}
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-center">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-[20px]">person_off</span>
                </div>
                <h4 class="text-slate-500 text-xs font-semibold mb-1 uppercase">Vắng mặt</h4>
                <div class="flex items-end gap-2">
                    <h2 class="text-2xl font-black text-slate-800">{{ number_format($absentGuests) }}</h2>
                    <span class="text-sm font-bold text-rose-500 mb-1">{{ 100 - $attendanceRate }}%</span>
                </div>
            </div>

            {{-- Đỉnh lưu lượng --}}
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-center">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-[20px]">local_fire_department</span>
                </div>
                <h4 class="text-slate-500 text-xs font-semibold mb-1 uppercase">Giờ cao điểm</h4>
                <div class="flex items-end gap-2">
                    <h2 class="text-2xl font-black text-slate-800">{{ $peakLabel }}</h2>
                    <span class="text-xs font-medium text-slate-400 mb-1.5">({{ $peakValue }} lượt)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ZONE 2: BIỂU ĐỒ LƯU LƯỢNG VÀ TỶ LỆ --}}
    <div>
        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">show_chart</span> Zone 2: Lưu lượng & Tỷ lệ
        </h2>
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Lưu lượng điểm danh</h3>
                        <p class="text-sm text-slate-500">Mật độ người tham dự AI qua các mốc thời gian</p>
                    </div>
                </div>
                <div class="w-full h-[280px] relative">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col">
                <h3 class="text-lg font-bold text-slate-800 mb-2">Tỷ lệ Tham dự</h3>
                <p class="text-sm text-slate-500 mb-4">Mức độ phủ kín sự kiện</p>
                
                <div class="w-full h-[200px] relative flex-1 flex justify-center items-center">
                    <canvas id="ratioChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-2">
                        <span class="text-3xl font-black text-slate-800">{{ $attendanceRate }}%</span>
                    </div>
                </div>
                
                <div class="flex justify-center gap-6 mt-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <span class="text-sm font-semibold text-slate-600">Có mặt</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-slate-200"></span>
                        <span class="text-sm font-semibold text-slate-600">Vắng</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ZONE 3: ĐỊA ĐIỂM & CHỨC VỤ --}}
    <div>
        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">insights</span> Zone 3: Phân tích Chuyên sâu
        </h2>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-slate-800">Hiệu suất theo Địa điểm (Top 5)</h3>
                    <p class="text-sm text-slate-500">So sánh số lượng khách đăng ký và thực tế có mặt</p>
                </div>
                <div class="w-full h-[250px] relative">
                    <canvas id="locationChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-slate-800">Phân bổ Chức vụ Đại biểu</h3>
                    <p class="text-sm text-slate-500">Top 5 chức vụ tham gia nhiều nhất trong kỳ</p>
                </div>
                <div class="space-y-5">
                    @php 
                        $maxPos = $topPositions->max('total') ?? 1; 
                        $colors = ['from-indigo-500 to-indigo-600', 'from-blue-400 to-blue-500', 'from-sky-400 to-sky-500', 'from-emerald-400 to-emerald-500', 'from-amber-400 to-amber-500'];
                    @endphp
                    @forelse($topPositions as $index => $pos)
                        @php $width = ($pos->total / $maxPos) * 100; @endphp
                        <div>
                            <div class="flex justify-between items-center text-sm font-bold mb-1">
                                <span class="text-slate-700">{{ $pos->position }}</span>
                                <span class="text-slate-500">{{ $pos->total }} người</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-gradient-to-r {{ $colors[$index % 5] }} h-2 rounded-full transition-all" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-slate-400 py-8">Chưa có dữ liệu chức vụ</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ZONE 4: GIÁM SÁT CHI TIẾT SỰ KIỆN --}}
    <div>
        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">table_chart</span> Zone 4: Giám sát Sự kiện
        </h2>
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 shrink-0">
                <h3 class="text-base font-bold text-slate-800">Danh sách Sự kiện gần đây</h3>
                <a href="{{ route('meetings.index') }}" data-no-swup class="text-xs font-semibold text-indigo-600 hover:underline">Xem toàn bộ Kho lưu trữ</a>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left">
                    <thead class="bg-slate-50/80 sticky top-0 z-10">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Tên & Địa điểm</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center min-w-[250px]">Tiến độ Check-in</th>
                            <th class="px-6 py-4 text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($detailedMeetings as $meeting)
                            @php $percent = $meeting->total_guests > 0 ? round(($meeting->checked_in_count / $meeting->total_guests) * 100) : 0; @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800 text-sm line-clamp-1" title="{{ $meeting->title }}">{{ $meeting->title }}</div>
                                    <div class="text-[12px] text-slate-500 mt-1 line-clamp-1 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">schedule</span> {{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i - d/m/Y') }}
                                        <span class="mx-1">|</span>
                                        <span class="material-symbols-outlined text-[14px]">location_on</span> {{ $meeting->location }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-between items-center text-xs mb-1.5">
                                        <span class="font-bold text-indigo-600">{{ $meeting->checked_in_count }} / {{ $meeting->total_guests }} đại biểu</span>
                                        <span class="font-semibold text-slate-500">{{ $percent }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2">
                                        <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ $percent }}%"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('meetings.show', $meeting->id) }}" data-no-swup class="px-4 py-2 bg-slate-100 text-slate-600 hover:bg-indigo-600 hover:text-white font-semibold text-xs rounded-xl inline-flex items-center gap-1 transition-colors">
                                        Quản lý <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-12 text-center text-slate-400 text-sm font-medium">Không có dữ liệu sự kiện trong khoảng thời gian này.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ZONE 5: QUẢN LÝ LỊCH TRÌNH CHUYÊN NGHIỆP (TÁCH BIỆT RỘNG RÃI) --}}
    <div>
        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">calendar_month</span> Zone 5: Quản lý Lịch trình Tổng thể
        </h2>
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 h-[600px]">
            
            {{-- Lịch trình dạng Timeline (Chiếm 1 cột) --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col overflow-hidden">
                <h3 class="text-base font-bold text-slate-800 mb-6 shrink-0 flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-500">pending_actions</span> Sắp diễn ra
                </h3>
                <div class="flex-1 overflow-y-auto pr-2 space-y-5">
                    @forelse($upcomingMeetings as $up)
                        @php
                            $start = \Carbon\Carbon::parse($up->start_time);
                            $isOngoing = $start->isPast() && \Carbon\Carbon::parse($up->end_time)->isFuture();
                        @endphp
                        <div class="relative pl-6 pb-5 border-l-2 {{ $isOngoing ? 'border-emerald-400' : 'border-indigo-100' }} last:border-0 last:pb-0">
                            <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full border-4 border-white {{ $isOngoing ? 'bg-emerald-500 animate-pulse' : 'bg-indigo-400' }}"></div>
                            <div class="text-[12px] font-bold {{ $isOngoing ? 'text-emerald-600' : 'text-indigo-600' }} mb-1.5 flex items-center gap-2">
                                {{ $start->format('d/m/Y - H:i') }}
                                @if($isOngoing) <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-md text-[10px] uppercase tracking-wider">Đang live</span> @endif
                            </div>
                            <h4 class="font-bold text-sm text-slate-800 line-clamp-2 mb-1.5 leading-snug">
                                <a href="{{ route('meetings.show', $up->id) }}" data-no-swup class="hover:text-indigo-600 transition-colors">{{ $up->title }}</a>
                            </h4>
                            <div class="text-xs text-slate-500 flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">location_on</span> {{ Str::limit($up->location, 25) }}</div>
                        </div>
                    @empty
                        <div class="text-center flex flex-col items-center justify-center text-slate-400 h-full">
                            <span class="material-symbols-outlined text-[48px] mb-2 opacity-50">event_busy</span>
                            <span class="text-sm font-medium">Chưa có lịch trình sắp tới.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Cuốn Lịch FullCalendar (Chiếm 2 cột - Giao diện rộng rãi) --}}
            <div class="xl:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6 overflow-hidden">
                <style>
                    .fc-theme-standard th { border: none !important; font-family: 'Plus Jakarta Sans'; font-size: 13px; color: #64748b; text-transform: uppercase; padding: 10px 0;}
                    .fc-theme-standard td, .fc-theme-standard .fc-scrollgrid { border-color: #f1f5f9; }
                    .fc-daygrid-day-number { font-size: 14px; font-weight: 700; color: #334155; padding: 8px !important;}
                    .fc-event { border-radius: 6px; padding: 3px 6px; font-size: 11px; cursor: pointer; transition: transform 0.2s; border: none !important; margin-bottom: 3px;}
                    .fc-event:hover { transform: scale(1.02); filter: brightness(1.1); }
                    .fc-toolbar-title { font-size: 18px !important; font-weight: 800; color: #1e293b; text-transform: capitalize; }
                    .fc-button-primary { background-color: #f8fafc !important; border-color: #e2e8f0 !important; color: #475569 !important; font-weight: bold; border-radius: 10px !important; text-transform: capitalize; padding: 6px 14px !important; transition: all 0.3s;}
                    .fc-button-primary:hover { background-color: #e2e8f0 !important; color: #1e293b !important; }
                    .fc-button-active { background-color: #4f46e5 !important; color: white !important; border-color: #4f46e5 !important; }
                    .fc-day-today { background-color: #f8fafc !important; }
                </style>
                <div id="mini-calendar" class="h-full w-full"></div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
    // [QUAN TRỌNG] Gắn biến vào window để tránh lỗi "Identifier has already been declared" khi Swup đổi trang
    window.attendanceChartInstance = window.attendanceChartInstance || null;
    window.ratioChartInstance = window.ratioChartInstance || null;
    window.locationChartInstance = window.locationChartInstance || null;
    window.calendarInstance = window.calendarInstance || null;

    function initDashboardCharts() {
        // 1. DỌN DẸP CANVAS CŨ TRƯỚC KHI VẼ MỚI (CHỐNG LỖI TREO BỘ NHỚ)
        if (window.attendanceChartInstance) { window.attendanceChartInstance.destroy(); }
        if (window.ratioChartInstance) { window.ratioChartInstance.destroy(); }
        if (window.locationChartInstance) { window.locationChartInstance.destroy(); }
        if (window.calendarInstance) { window.calendarInstance.destroy(); }

        const tooltipConfig = { backgroundColor: '#1e293b', padding: 12, titleFont: { family: 'Plus Jakarta Sans', size: 13 }, bodyFont: { family: 'Plus Jakarta Sans', size: 14, weight: 'bold' }, cornerRadius: 8 };

        // 2. VẼ BIỂU ĐỒ LINE
        const lineCanvas = document.getElementById('attendanceChart');
        if (lineCanvas) {
            const ctxLine = lineCanvas.getContext('2d');
            let gradientLine = ctxLine.createLinearGradient(0, 0, 0, 300);
            gradientLine.addColorStop(0, 'rgba(79, 70, 229, 0.4)'); 
            gradientLine.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

            window.attendanceChartInstance = new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartLabels) !!}, 
                    datasets: [{ label: 'Lượt Check-in', data: {!! json_encode($chartData) !!}, borderColor: '#4f46e5', backgroundColor: gradientLine, borderWidth: 3, pointBackgroundColor: '#fff', pointBorderColor: '#4f46e5', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6, fill: true, tension: 0.4 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: Object.assign({}, tooltipConfig, { displayColors: false }) }, scales: { x: { grid: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans' }, color: '#64748b' } }, y: { grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Plus Jakarta Sans' }, color: '#64748b', stepSize: 1, precision: 0 }, beginAtZero: true } } }
            });
        }

        // 3. VẼ BIỂU ĐỒ DOUGHNUT
        const pieCanvas = document.getElementById('ratioChart');
        if (pieCanvas) {
            window.ratioChartInstance = new Chart(pieCanvas.getContext('2d'), {
                type: 'doughnut',
                data: { labels: ['Có mặt', 'Vắng mặt'], datasets: [{ data: [{{ $checkedInGuests ?? 0 }}, {{ $absentGuests ?? 0 }}], backgroundColor: ['#10b981', '#f1f5f9'], borderWidth: 0, hoverOffset: 4 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '78%', plugins: { legend: { display: false }, tooltip: tooltipConfig } }
            });
        }

        // 4. VẼ BIỂU ĐỒ BAR
        const barCanvas = document.getElementById('locationChart');
        if (barCanvas) {
            window.locationChartInstance = new Chart(barCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($locLabels) !!},
                    datasets: [
                        { label: 'Đã check-in', data: {!! json_encode($locCheckedData) !!}, backgroundColor: '#4f46e5', borderRadius: 6, barPercentage: 0.6 },
                        { label: 'Tổng đăng ký', data: {!! json_encode($locTotalData) !!}, backgroundColor: '#e2e8f0', borderRadius: 6, barPercentage: 0.6 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: { family: 'Plus Jakarta Sans', weight: 'bold' }, usePointStyle: true, padding: 20 } }, tooltip: tooltipConfig }, scales: { x: { grid: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans', size: 11 }, color: '#64748b' } }, y: { grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Plus Jakarta Sans' }, color: '#64748b', stepSize: 1, precision: 0 }, beginAtZero: true } } }
            });
        }

        // 5. KHỞI TẠO CUỐN LỊCH
        const calendarEl = document.getElementById('mini-calendar');
        if (calendarEl) {
            window.calendarInstance = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'vi',
                height: '100%',
                headerToolbar: { left: 'prev,next', center: 'title', right: 'today' },
                buttonText: { today: 'Hôm nay' },
                events: {!! json_encode($calendarEvents) !!},
                eventClick: function(info) {
                    info.jsEvent.preventDefault(); 
                    if (info.event.url) { window.location.href = info.event.url; }
                }
            });
            window.calendarInstance.render();
        }
    }

    // Cơ chế an toàn kích hoạt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardCharts);
    } else {
        initDashboardCharts();
    }

    // Trigger vẽ lại biểu đồ khi Swup load xong nội dung HTML mới
    if (typeof swup !== 'undefined') {
        swup.hooks.on('page:view', initDashboardCharts);
    }
</script>
@endsection