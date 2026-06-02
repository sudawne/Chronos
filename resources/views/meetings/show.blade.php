@extends('layouts.app')

@section('title', 'Chi Tiết Cuộc Họp | AI Attendance')
@section('page_title', $meeting->title)
@section('page_subtitle', 'Quản lý thông tin chi tiết và danh sách thành viên đăng ký khuôn mặt')

@section('content')
<div class="px-4 lg:px-8 pb-12 bg-slate-50/50 min-h-screen relative">
    
    {{-- Header Section --}}
    <div class="mb-8">
        {{-- Back Button --}}
        <a href="{{ route('meetings.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl shadow-sm text-slate-600 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50/50 transition-all duration-300 mb-6 group">
            <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
            <span class="text-sm font-medium">Quay lại danh sách</span>
        </a>

        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
            {{-- Title Area --}}
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-2 h-8 bg-indigo-600 rounded-full"></div>
                    <h1 class="text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight">{{ $meeting->title }}</h1>
                </div>
                <p class="text-slate-500 text-base ml-5">Quản lý thông tin chi tiết và danh sách thành viên đăng ký khuôn mặt</p>
            </div>
            
            {{-- THANH CÔNG CỤ (ACTION BAR) --}}
            <div class="flex flex-col gap-3 shrink-0 lg:items-end w-full lg:w-auto mt-2 lg:mt-0">
                
                {{-- Nhóm 1: Hệ thống Điểm danh (Webcam & QR) --}}
                <div class="flex flex-wrap items-center p-1.5 bg-white border border-slate-200/80 rounded-2xl shadow-sm gap-1 w-full lg:w-auto">
                    <div class="px-3 flex items-center gap-2 border-r border-slate-100 hidden sm:flex">
                        <span class="relative flex h-2.5 w-2.5">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-600"></span>
                        </span>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Điểm danh</span>
                    </div>
                    
                    {{-- Nút Webcam được đẩy lên làm nút chính yếu --}}
                    <a href="{{ route('meetings.online', $meeting->id) }}" target="_blank" 
                       class="flex-1 sm:flex-none flex justify-center items-center gap-2 px-5 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-sm shadow-blue-600/20 transition-all duration-300 active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">photo_camera</span>
                        <span class="text-sm font-semibold">Webcam Điểm danh</span>
                    </a>

                    <a href="{{ route('meetings.scan_qr', $meeting->id) }}" target="_blank" 
                       class="flex-1 sm:flex-none flex justify-center items-center gap-2 px-4 py-2 bg-transparent text-slate-600 hover:bg-slate-50 hover:text-rose-600 rounded-xl transition-all duration-300 active:scale-95" title="Quét bằng điện thoại">
                        <span class="material-symbols-outlined text-[18px] text-rose-500">qr_code_scanner</span>
                        <span class="text-sm font-medium">Quét QR</span>
                    </a>
                </div>

                {{-- Nhóm 2: Màn hình & Tiện ích --}}
                <div class="flex flex-wrap items-center p-1.5 bg-white border border-slate-200/80 rounded-2xl shadow-sm gap-1 w-full lg:w-auto">
                    <div class="px-3 flex items-center gap-2 border-r border-slate-100 hidden sm:flex">
                        <span class="material-symbols-outlined text-[16px] text-slate-400">widgets</span>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Tiện ích</span>
                    </div>

                    <a href="{{ route('meetings.welcome', $meeting->id) }}" target="_blank" 
                       class="flex-1 sm:flex-none flex justify-center items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl hover:bg-indigo-100 transition-all duration-300 active:scale-95 border border-indigo-100/50">
                        <span class="material-symbols-outlined text-[18px]">desktop_windows</span>
                        <span class="text-sm font-bold">Welcome</span>
                    </a>

                    <a href="{{ route('meetings.designer', $meeting->id) }}" target="_blank" 
                       class="flex-1 sm:flex-none flex justify-center items-center gap-2 px-3 py-2 bg-transparent text-slate-600 hover:bg-slate-50 hover:text-fuchsia-600 rounded-xl transition-all duration-300 active:scale-95">
                        <span class="material-symbols-outlined text-[18px] text-fuchsia-500">brush</span>
                        <span class="text-sm font-medium">Design</span>
                    </a>

                    <a href="{{ route('meetings.game', $meeting->id) }}" target="_blank" 
                       class="flex-1 sm:flex-none flex justify-center items-center gap-2 px-3 py-2 bg-transparent text-slate-600 hover:bg-slate-50 hover:text-orange-600 rounded-xl transition-all duration-300 active:scale-95">
                        <span class="material-symbols-outlined text-[18px] text-orange-500">sports_esports</span>
                        <span class="text-sm font-medium">Game AI</span>
                    </a>

                    <div class="w-px h-5 bg-slate-200 mx-1 hidden sm:block"></div>

                    <form action="{{ route('meetings.send_tickets', $meeting->id) }}" method="POST" class="inline m-0 flex-1 sm:flex-none" onsubmit="return confirm('Hệ thống sẽ tiến hành gửi vé QR cho tất cả đại biểu CÓ email. Quá trình này có thể mất vài phút. Bạn có chắc chắn không?');">
                        @csrf
                        <button type="submit" class="w-full flex justify-center items-center gap-2 px-3 py-2 bg-transparent text-slate-600 hover:bg-slate-50 hover:text-amber-600 rounded-xl transition-all duration-300 active:scale-95">
                            <span class="material-symbols-outlined text-[18px] text-amber-500">forward_to_inbox</span>
                            <span class="text-sm font-medium">Gửi Vé</span>
                        </button>
                    </form>
                </div>
            </div>
            
        </div>
    </div>

    {{-- Thông báo Alert --}}
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-100 p-4 rounded-2xl text-emerald-800 font-bold text-sm flex items-center gap-3 shadow-sm animate-fade-in-down">
            <span class="material-symbols-outlined text-emerald-500">check_circle</span> {{ session('success') }}
        </div>
    @elseif(session('error'))
        <div class="mb-6 bg-red-50 border border-red-100 p-4 rounded-2xl text-red-800 font-bold text-sm flex items-center gap-3 shadow-sm animate-fade-in-down">
            <span class="material-symbols-outlined text-red-500">error</span> {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-100 p-4 rounded-2xl text-red-800 font-bold text-sm shadow-sm animate-fade-in-down">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- Location Card --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/60 hover:shadow-md transition-shadow duration-300 flex items-start gap-5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-bl-full -z-10 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="w-14 h-14 rounded-2xl bg-indigo-100/80 flex items-center justify-center shrink-0 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                <span class="material-symbols-outlined text-[28px]">location_on</span>
            </div>
            <div class="pt-1">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Địa điểm</p>
                <h3 class="text-lg font-bold text-slate-800 leading-snug">{{ $meeting->location }}</h3>
            </div>
        </div>

        {{-- Time Card --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/60 hover:shadow-md transition-shadow duration-300 flex items-start gap-5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full -z-10 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="w-14 h-14 rounded-2xl bg-blue-100/80 flex items-center justify-center shrink-0 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                <span class="material-symbols-outlined text-[28px]">schedule</span>
            </div>
            <div class="pt-1 w-full">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Thời gian</p>
                <div class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Từ:</span>
                        <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i - d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Đến:</span>
                        <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i - d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Config Card --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/60 hover:shadow-md transition-shadow duration-300 flex items-start gap-5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-full -z-10 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="w-14 h-14 rounded-2xl bg-emerald-100/80 flex items-center justify-center shrink-0 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                <span class="material-symbols-outlined text-[28px]">memory</span>
            </div>
            <div class="pt-1">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Cấu hình AI</p>
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 mb-2 border border-slate-200">
                    <span class="text-xs font-medium">Ngưỡng ArcFace:</span>
                    <span class="text-sm font-bold text-emerald-600">{{ $meeting->recognition_threshold }}</span>
                </div>
                <p class="text-xs text-slate-500 italic line-clamp-2 leading-relaxed" title="{{ $meeting->description ?? 'Không có ghi chú.' }}">
                    {{ $meeting->description ?? 'Không có ghi chú thêm cho cuộc họp này.' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Guest List Section --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        {{-- Table Header --}}
        <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex flex-col xl:flex-row xl:justify-between xl:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                    <span class="material-symbols-outlined">groups</span>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Danh sách khách mời</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Chi tiết trạng thái điểm danh và dữ liệu khuôn mặt</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl shadow-sm">
                    <span class="material-symbols-outlined text-slate-400 text-[20px]">analytics</span>
                    <span class="text-sm font-medium">
                        Tổng số: <strong class="text-indigo-600 text-base">{{ $meeting->guests->count() }}</strong>
                    </span>
                </div>
                <button onclick="openAddGuestModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-xl shadow-sm hover:bg-indigo-700 transition-colors duration-300 active:scale-95">
                    <span class="material-symbols-outlined text-[20px]">person_add</span>
                    <span class="text-sm font-semibold">Thêm đại biểu</span>
                </button>
            </div>
        </div>
        
        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-16 text-center">STT</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Khách mời</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Chức vụ</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Vị trí ngồi</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Dữ liệu AI</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($meeting->guests as $index => $guest)
                        <tr class="hover:bg-slate-50/80 transition-colors duration-200 group">
                            <td class="px-6 py-4 text-center text-sm font-medium text-slate-400 group-hover:text-slate-600 transition-colors">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </td>
                            
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 border-2 border-white shadow-sm overflow-hidden flex items-center justify-center shrink-0">
                                            @if($guest->face_vector)
                                                <img class="w-full h-full object-cover" alt="{{ $guest->full_name }}" src="https://ui-avatars.com/api/?name={{ urlencode($guest->full_name) }}&color=4f46e5&background=e0e7ff&bold=true&size=128">
                                            @else
                                                <span class="material-symbols-outlined text-slate-400 text-[24px]">person_off</span>
                                            @endif
                                        </div>
                                        @if($guest->is_attended)
                                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full"></div>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="text-sm font-bold text-slate-800 block mb-0.5">{{ $guest->full_name }}</span>
                                        <span class="text-xs text-slate-500 block">{{ $guest->email ?? 'Chưa cập nhật email' }}</span>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-medium border border-slate-200">
                                    {{ $guest->position ?? 'Chưa cập nhật' }}
                                </span>
                            </td>
                            
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <span class="material-symbols-outlined text-[18px] text-slate-400">event_seat</span>
                                    <span class="{{ $guest->seat_location ? 'font-medium text-slate-700' : 'italic text-slate-400' }}">
                                        {{ $guest->seat_location ?? 'Tự do' }}
                                    </span>
                                </div>
                            </td>
                            
                            {{-- CỘT DỮ LIỆU AI --}}
                            <td class="px-6 py-4">
                                @if($guest->face_vector)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-lg text-xs font-medium">
                                        <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">verified</span>
                                        Đã nạp Vector (512d)
                                    </div>
                                @else
                                    <button type="button" onclick="openFaceModal({{ $guest->id }}, '{{ addslashes($guest->full_name) }}')" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg text-xs font-medium transition-colors shadow-sm cursor-pointer group/btn" 
                                            title="Bấm vào để cập nhật ảnh">
                                        <span class="material-symbols-outlined text-[16px] group-hover/btn:animate-bounce" style="font-variation-settings: 'FILL' 1;">add_a_photo</span> 
                                        Cập nhật ảnh
                                    </button>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 text-right">
                                @if($guest->is_attended)
                                    <span class="inline-flex items-center justify-center px-3 py-1.5 bg-emerald-500 text-white rounded-lg text-xs font-bold uppercase tracking-wide shadow-sm shadow-emerald-500/20">
                                        Đã Check-in
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center px-3 py-1.5 bg-slate-100 text-slate-500 rounded-lg text-xs font-bold uppercase tracking-wide border border-slate-200">
                                        Vắng mặt
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <div class="w-16 h-16 bg-white rounded-full border border-slate-100 shadow-sm flex items-center justify-center mb-4">
                                        <span class="material-symbols-outlined text-[32px] opacity-50">group_off</span>
                                    </div>
                                    <p class="text-base font-medium text-slate-600 mb-1">Chưa có khách mời</p>
                                    <p class="text-sm">Không có khách mời nào được thêm vào cuộc họp này.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination/Footer --}}
        @if($meeting->guests->count() > 0)
        <div class="p-4 sm:p-6 bg-slate-50 flex flex-col sm:flex-row items-center justify-between border-t border-slate-200 gap-4">
            <p class="text-sm text-slate-500">
                Hiển thị <span class="font-semibold text-slate-800">{{ $meeting->guests->count() }}</span> kết quả
            </p>
            
            <div class="flex items-center gap-2">
                <button class="w-9 h-9 rounded-lg flex items-center justify-center border border-slate-200 bg-white text-slate-400 cursor-not-allowed" disabled>
                    <span class="material-symbols-outlined text-[20px]">chevron_left</span>
                </button>
                <button class="w-9 h-9 rounded-lg flex items-center justify-center bg-indigo-600 text-white font-medium shadow-sm">1</button>
                <button class="w-9 h-9 rounded-lg flex items-center justify-center border border-slate-200 bg-white text-slate-400 cursor-not-allowed" disabled>
                    <span class="material-symbols-outlined text-[20px]">chevron_right</span>
                </button>
            </div>
        </div>
        @endif
    </div>
</div>

<div id="face-modal-backdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[100] hidden items-center justify-center opacity-0 transition-opacity duration-300">
    <div id="face-modal-box" class="bg-white rounded-3xl w-full max-w-lg mx-4 shadow-2xl transform scale-95 transition-all duration-300 overflow-hidden relative border border-slate-100">
        
        <div id="modal-loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-50 hidden flex-col items-center justify-center">
            <span class="material-symbols-outlined text-indigo-600 text-4xl animate-spin">model_training</span>
            <p class="font-bold text-indigo-600 mt-3 animate-pulse">Đang xử lý dữ liệu AI...</p>
        </div>

        <div class="p-6 md:p-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-600">face_retouching_natural</span>
                    Bổ sung dữ liệu AI
                </h3>
                <button type="button" onclick="closeFaceModal()" class="text-slate-400 hover:text-red-500 transition-colors bg-slate-100 hover:bg-red-50 w-8 h-8 rounded-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            
            <form id="face-update-form" action="" method="POST" enctype="multipart/form-data" onsubmit="showModalLoading('modal-loading')">
                @csrf
                <p class="text-slate-600 mb-5 text-sm">Cập nhật ảnh khuôn mặt cho đại biểu: <br><strong id="modal-guest-name" class="text-indigo-600 text-lg"></strong></p>
                
                <label for="single-image-upload" class="flex flex-col items-center justify-center w-full h-44 border-2 border-indigo-200 border-dashed rounded-2xl cursor-pointer bg-indigo-50/50 hover:bg-indigo-50 transition-all group mb-6 relative overflow-hidden">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4 z-10">
                        <span class="material-symbols-outlined text-4xl text-indigo-500 mb-2 group-hover:scale-110 transition-transform">add_a_photo</span>
                        <p class="mb-1 text-sm text-slate-700 font-bold" id="upload-text">Nhấn để chọn 1 bức ảnh rõ mặt</p>
                        <p class="text-xs text-slate-500">Nên chọn ảnh chụp thẳng, không đeo kính râm.</p>
                    </div>
                    <img id="image-preview" src="" class="absolute inset-0 w-full h-full object-contain opacity-0 transition-opacity z-0" />
                    <input id="single-image-upload" name="file_anh" type="file" class="sr-only" accept="image/jpeg, image/png" required onchange="previewImage(this, 'upload-text', 'image-preview')" />
                </label>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-5 mt-2">
                    <button type="button" onclick="closeFaceModal()" class="px-5 py-2.5 bg-slate-100 text-slate-700 font-medium rounded-xl hover:bg-slate-200 transition-colors">Hủy</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 flex items-center gap-2 hover:-translate-y-0.5 transition-all">
                        <span class="material-symbols-outlined text-[18px]">memory</span> Trích xuất Vector
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="add-guest-modal-backdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[100] hidden items-center justify-center opacity-0 transition-opacity duration-300 overflow-y-auto">
    <div id="add-guest-modal-box" class="bg-white rounded-3xl w-full max-w-2xl mx-4 my-8 shadow-2xl transform scale-95 transition-all duration-300 relative border border-slate-100">
        
        <div id="add-guest-loading" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-50 hidden flex-col items-center justify-center rounded-3xl">
            <span class="material-symbols-outlined text-indigo-600 text-4xl animate-spin">model_training</span>
            <p class="font-bold text-indigo-600 mt-3 animate-pulse">Đang thêm đại biểu & phân tích AI...</p>
        </div>

        <div class="p-6 md:p-8">
            <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-indigo-600">person_add</span>
                    Thêm đại biểu mới
                </h3>
                <button type="button" onclick="closeAddGuestModal()" class="text-slate-400 hover:text-red-500 transition-colors bg-slate-100 hover:bg-red-50 w-8 h-8 rounded-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            
            <form action="{{ route('meetings.add_guest', $meeting->id) }}" method="POST" enctype="multipart/form-data" onsubmit="showModalLoading('add-guest-loading')">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div class="col-span-1 md:col-span-2 space-y-1.5">
                        <label class="text-sm font-semibold text-slate-700">Họ và Tên <span class="text-red-500">*</span></label>
                        <input type="text" name="full_name" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 transition-colors" placeholder="Nguyễn Văn A">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-slate-700">Email (Tùy chọn)</label>
                        <input type="email" name="email" class="w-full bg-slate-50 border border-slate-200 text-slate-900 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 transition-colors" placeholder="abc@email.com">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-slate-700">Chức vụ (Tùy chọn)</label>
                        <input type="text" name="position" class="w-full bg-slate-50 border border-slate-200 text-slate-900 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 transition-colors" placeholder="Giám đốc">
                    </div>

                    <div class="col-span-1 md:col-span-2 space-y-1.5">
                        <label class="text-sm font-semibold text-slate-700">Vị trí ghế (Tùy chọn)</label>
                        <input type="text" name="seat_location" class="w-full bg-slate-50 border border-slate-200 text-slate-900 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 transition-colors" placeholder="Dãy A - Ghế 01">
                    </div>
                </div>

                <div class="space-y-1.5 mb-6">
                    <label class="text-sm font-semibold text-slate-700 flex items-center justify-between">
                        <span>Ảnh nhận diện (Tùy chọn)</span>
                        <span class="text-xs text-slate-400 font-normal">Có thể nạp sau</span>
                    </label>
                    <label for="new-guest-image" class="flex flex-col items-center justify-center w-full h-32 border-2 border-indigo-200 border-dashed rounded-2xl cursor-pointer bg-indigo-50/50 hover:bg-indigo-50 transition-all group relative overflow-hidden">
                        <div class="flex flex-col items-center justify-center text-center px-4 z-10">
                            <span class="material-symbols-outlined text-3xl text-indigo-400 mb-1 group-hover:scale-110 transition-transform">add_a_photo</span>
                            <p class="text-sm text-slate-600 font-medium" id="new-guest-upload-text">Tải lên ảnh khuôn mặt</p>
                        </div>
                        <img id="new-guest-preview" src="" class="absolute inset-0 w-full h-full object-contain opacity-0 transition-opacity z-0" />
                        <input id="new-guest-image" name="file_anh" type="file" class="sr-only" accept="image/jpeg, image/png" onchange="previewImage(this, 'new-guest-upload-text', 'new-guest-preview')" />
                    </label>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-5 mt-2">
                    <button type="button" onclick="closeAddGuestModal()" class="px-5 py-2.5 bg-slate-100 text-slate-700 font-medium rounded-xl hover:bg-slate-200 transition-colors">Hủy</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 flex items-center gap-2 hover:-translate-y-0.5 transition-all">
                        <span class="material-symbols-outlined text-[18px]">save</span> Lưu đại biểu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openFaceModal(guestId, guestName) {
        const backdrop = document.getElementById('face-modal-backdrop');
        const box = document.getElementById('face-modal-box');
        const form = document.getElementById('face-update-form');
        
        form.action = `/guests/${guestId}/update-face`; 
        document.getElementById('modal-guest-name').innerText = guestName;
        
        document.getElementById('single-image-upload').value = '';
        document.getElementById('upload-text').innerText = 'Nhấn để chọn 1 bức ảnh rõ mặt';
        const preview = document.getElementById('image-preview');
        preview.src = '';
        preview.classList.add('opacity-0');

        backdrop.classList.remove('hidden');
        backdrop.classList.add('flex');
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }, 10);
    }

    function closeFaceModal() {
        const backdrop = document.getElementById('face-modal-backdrop');
        const box = document.getElementById('face-modal-box');
        
        backdrop.classList.add('opacity-0');
        box.classList.remove('scale-100');
        box.classList.add('scale-95');
        setTimeout(() => {
            backdrop.classList.add('hidden');
            backdrop.classList.remove('flex');
        }, 300); 
    }

    function openAddGuestModal() {
        const backdrop = document.getElementById('add-guest-modal-backdrop');
        const box = document.getElementById('add-guest-modal-box');
        
        document.getElementById('new-guest-image').value = '';
        document.getElementById('new-guest-upload-text').innerText = 'Tải lên ảnh khuôn mặt';
        const preview = document.getElementById('new-guest-preview');
        preview.src = '';
        preview.classList.add('opacity-0');

        backdrop.classList.remove('hidden');
        backdrop.classList.add('flex');
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }, 10);
    }

    function closeAddGuestModal() {
        const backdrop = document.getElementById('add-guest-modal-backdrop');
        const box = document.getElementById('add-guest-modal-box');
        
        backdrop.classList.add('opacity-0');
        box.classList.remove('scale-100');
        box.classList.add('scale-95');
        setTimeout(() => {
            backdrop.classList.add('hidden');
            backdrop.classList.remove('flex');
        }, 300); 
    }

    function previewImage(input, textElementId, previewElementId) {
        const textLabel = document.getElementById(textElementId);
        const preview = document.getElementById(previewElementId);

        if(input.files && input.files[0]) {
            textLabel.innerHTML = `<span class="bg-white/90 px-2 py-1 rounded backdrop-blur-sm">Đã chọn: <strong class="text-indigo-600">${input.files[0].name}</strong></span>`;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('opacity-0');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function showModalLoading(elementId) {
        document.getElementById(elementId).classList.remove('hidden');
        document.getElementById(elementId).classList.add('flex');
    }
</script>
@endsection