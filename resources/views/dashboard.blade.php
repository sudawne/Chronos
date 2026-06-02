@extends('layouts.app')

@section('title', 'Trung tâm Chỉ huy | CHRONOS AI')

@section('content')
<div class="px-4 lg:px-8 pb-12 bg-slate-50/50 min-h-screen space-y-6">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-2">
        <div class="inline-flex bg-white rounded-xl shadow-sm border border-slate-200 p-1">
            <a href="{{ route('dashboard', ['range' => 'today']) }}" class="px-4 py-2 rounded-lg text-sm transition-colors {{ $range == 'today' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700 font-medium' }}">Hôm nay</a>
            <a href="{{ route('dashboard', ['range' => 'week']) }}" class="px-4 py-2 rounded-lg text-sm transition-colors {{ $range == 'week' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700 font-medium' }}">Tuần này</a>
            <a href="{{ route('dashboard', ['range' => 'month']) }}" class="px-4 py-2 rounded-lg text-sm transition-colors {{ $range == 'month' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700 font-medium' }}">Tháng này</a>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('meetings.create') }}" class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl shadow-sm hover:bg-indigo-700 transition-all font-semibold text-sm active:scale-95">
                <span class="material-symbols-outlined text-[20px]">add</span> Tạo sự kiện
            </a>
            <a href="{{ route('meetings.index') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl shadow-sm hover:bg-slate-50 transition-all font-semibold text-sm active:scale-95">
                <span class="material-symbols-outlined text-[20px]">folder_open</span> Kho lưu trữ
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-bl-full -z-10 group-hover:scale-110 transition-transform"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <span class="material-symbols-outlined">event_available</span>
                </div>
            </div>
            <h4 class="text-slate-500 text-sm font-semibold mb-1">Tổng sự kiện</h4>
            <h2 class="text-3xl font-black text-slate-800">{{ number_format($totalMeetings) }}</h2>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full -z-10 group-hover:scale-110 transition-transform"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center">
                    <span class="material-symbols-outlined">groups</span>
                </div>
            </div>
            <h4 class="text-slate-500 text-sm font-semibold mb-1">Tổng đại biểu</h4>
            <h2 class="text-3xl font-black text-slate-800">{{ number_format($totalGuests) }}</h2>
        </div>

        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-full -z-10 group-hover:scale-110 transition-transform"></div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined">how_to_reg</span>
                </div>
                @if($attendanceRate >= 80)
                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">trending_up</span> Cao</span>
                @else
                    <span class="px-2.5 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-bold flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">trending_flat</span> Trung bình</span>
                @endif
            </div>
            <h4 class="text-slate-500 text-sm font-semibold mb-1">Tỷ lệ Check-in (TB)</h4>
            <h2 class="text-3xl font-black text-slate-800">{{ $attendanceRate }}%</h2>
        </div>

        <div class="bg-gradient-to-br from-[#5949be] to-[#6C5DD3] p-6 rounded-3xl shadow-lg shadow-indigo-600/20 text-white relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 opacity-20 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-[120px]">memory</span>
            </div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur text-white flex items-center justify-center">
                        <span class="material-symbols-outlined">speed</span>
                    </div>
                    <span class="px-2.5 py-1 bg-white/20 rounded-lg text-xs font-bold flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Online
                    </span>
                </div>
                <h4 class="text-indigo-100 text-sm font-medium mb-1">Độ chính xác AI (ArcFace)</h4>
                <div class="flex items-baseline gap-1">
                    <h2 class="text-3xl font-black">98.5%</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <div class="xl:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Lưu lượng Check-in Hôm Nay</h3>
                    <p class="text-sm text-slate-500">Phân tích mật độ điểm danh theo khung giờ thực tế</p>
                </div>
            </div>
            <div class="w-full h-[300px] relative">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-slate-800">Địa điểm sự kiện</h3>
                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold">Top khu vực</span>
            </div>
            
            <div class="w-full h-40 bg-slate-100 rounded-2xl mb-6 relative overflow-hidden flex items-center justify-center border border-slate-200">
                <span class="material-symbols-outlined text-[64px] text-slate-300">map</span>
                <div class="absolute top-10 left-20">
                    <span class="flex h-4 w-4 relative"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span><span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500 border-2 border-white"></span></span>
                </div>
                <div class="absolute bottom-10 right-24">
                    <span class="flex h-3 w-3 relative"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500 border-2 border-white"></span></span>
                </div>
            </div>

            <div class="space-y-4 flex-1">
                @php $colors = ['rose', 'indigo', 'emerald']; @endphp
                @forelse($topLocations as $index => $loc)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-{{ $colors[$index % 3] }}-500"></div>
                            <span class="font-semibold text-slate-700 text-sm">{{ $loc->location }}</span>
                        </div>
                        <span class="font-bold text-slate-800 text-sm">{{ $loc->total }} sự kiện</span>
                    </div>
                @empty
                    <p class="text-slate-400 text-sm text-center mt-4">Chưa có dữ liệu địa điểm</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <div class="xl:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-600">calendar_month</span> Lịch trình sắp tới
                </h3>
                <a href="{{ route('meetings.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Xem tất cả &rarr;</a>
            </div>

            <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">
                
                @forelse($upcomingMeetings as $meeting)
                    @php
                        $start = \Carbon\Carbon::parse($meeting->start_time, 'Asia/Ho_Chi_Minh');
                        $end = \Carbon\Carbon::parse($meeting->end_time, 'Asia/Ho_Chi_Minh');
                        $isOngoing = $now->between($start, $end);
                    @endphp

                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group {{ $isOngoing ? 'is-active' : '' }}">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10 {{ $isOngoing ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500' }}">
                            @if($isOngoing)
                                <span class="w-3 h-3 bg-indigo-600 rounded-full animate-pulse"></span>
                            @else
                                <span class="material-symbols-outlined text-[18px]">schedule</span>
                            @endif
                        </div>
                        
                        <a href="{{ route('meetings.show', $meeting->id) }}" class="block w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-2xl border shadow-sm transition-all hover:-translate-y-1 {{ $isOngoing ? 'bg-indigo-50/50 border-indigo-200 hover:shadow-md' : 'bg-white border-slate-200 hover:bg-slate-50' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold uppercase {{ $isOngoing ? 'text-indigo-600' : 'text-slate-500' }}">
                                    {{ $isOngoing ? 'Đang diễn ra' : ($start->isToday() ? 'Hôm nay' : $start->format('d/m/Y')) }}
                                </span>
                                <span class="text-xs font-semibold text-slate-500">{{ $start->format('H:i') }} - {{ $end->format('H:i') }}</span>
                            </div>
                            <h4 class="font-bold text-slate-800 mb-1 line-clamp-1" title="{{ $meeting->title }}">{{ $meeting->title }}</h4>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-sm text-slate-500 flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">location_on</span> {{ Str::limit($meeting->location, 20) }}</p>
                                <span class="text-xs font-bold text-indigo-600 bg-indigo-100 px-2 py-1 rounded">{{ $meeting->guests_count }} đại biểu</span>
                            </div>
                        </a>
                    </div>
                @empty
                    <p class="text-center text-slate-400 py-4 font-medium">Không có lịch trình sự kiện nào sắp tới.</p>
                @endforelse

            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-500">health_and_safety</span> System Health
            </h3>

            <div class="space-y-5">
                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-bold text-slate-700 flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-blue-500">dataset</span> RAM Usage (YOLOv8)</span>
                        <span class="text-xs font-bold text-slate-500">Ổn định</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: 45%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-bold text-slate-700 flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-indigo-500">memory_alt</span> CPU Load (ArcFace)</span>
                        <span class="text-xs font-bold text-slate-500">Nhẹ</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: 25%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="text-sm font-bold text-slate-700 flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-emerald-500">dns</span> Database Connection</span>
                        <span class="text-xs font-bold text-emerald-500">Kết nối tốt</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full" style="width: 100%"></div>
                    </div>
                </div>
            </div>

            <div class="mt-8 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                <h4 class="text-xs font-bold text-slate-500 uppercase mb-2">Thao tác nhanh Server</h4>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('api.start_server') }}" class="py-2 text-center bg-white border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 hover:border-indigo-500 hover:text-indigo-600 transition-colors shadow-sm cursor-pointer">
                        Bật Server AI
                    </a>
                    <a href="{{ route('meetings.index') }}" class="py-2 text-center bg-white border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 hover:border-emerald-500 hover:text-emerald-600 transition-colors shadow-sm cursor-pointer">
                        Quản lý Cuộc họp
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        
        let gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(89, 73, 190, 0.4)');
        gradient.addColorStop(1, 'rgba(89, 73, 190, 0.0)');

        // NHẬN DỮ LIỆU ĐỘNG TỪ PHP CONTROLLER
        const labelsData = {!! json_encode($chartLabels) !!};
        const chartData = {!! json_encode($chartData) !!};

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labelsData, // Mảng giờ (VD: ['07:00', '08:00',...])
                datasets: [{
                    label: 'Số lượng Check-in',
                    data: chartData, // Mảng số liệu đếm từ database
                    borderColor: '#5949be',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#5949be',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }, 
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleFont: { family: 'Plus Jakarta Sans', size: 13 },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 14, weight: 'bold' },
                        displayColors: false,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { font: { family: 'Plus Jakarta Sans' }, color: '#94a3b8' }
                    },
                    y: {
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { font: { family: 'Plus Jakarta Sans' }, color: '#94a3b8', stepSize: 10, precision: 0 },
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endsection