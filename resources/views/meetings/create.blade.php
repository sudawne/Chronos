@extends('layouts.app')

@section('title', 'Tạo Sự Kiện Mới | AI Attendance')
@section('page_title', 'Tạo sự kiện mới')
@section('page_subtitle', 'Thiết lập thông tin sự kiện và nạp dữ liệu khuôn mặt cho hệ thống AI')

@section('content')
<div class="max-w-6xl mx-auto bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up">
    
    <div class="p-8 md:p-12">
        
        <form action="{{ route('meetings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-10">
            @csrf
            
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
                    <div class="flex items-start">
                        <span class="material-symbols-outlined text-red-500 mr-2">error</span>
                        <div>
                            <h3 class="text-red-800 font-bold mb-1">Vui lòng kiểm tra lại dữ liệu nhập vào:</h3>
                            <ul class="list-disc list-inside text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2 border-b pb-4">
                    <span class="material-symbols-outlined text-[#5949be]">event_note</span>
                    Thông tin Sự kiện
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 md:col-span-2">
                        <label class="font-semibold text-gray-700 text-sm">Tên sự kiện / Cuộc họp <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required 
                               class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-[#5949be] focus:border-[#5949be] block p-4 transition-colors" 
                               placeholder="Ví dụ: Hội nghị Chuyển đổi số 2026">
                    </div>

                    <div class="space-y-2">
                        <label class="font-semibold text-gray-700 text-sm">Thời gian bắt đầu <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" required 
                               class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-[#5949be] focus:border-[#5949be] block p-4 transition-colors">
                    </div>

                    <div class="space-y-2">
                        <label class="font-semibold text-gray-700 text-sm">Thời gian kết thúc <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" required 
                               class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-[#5949be] focus:border-[#5949be] block p-4 transition-colors">
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="font-semibold text-gray-700 text-sm">Địa điểm tổ chức <span class="text-red-500">*</span></label>
                        <input type="text" name="location" value="{{ old('location') }}" required 
                               class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-[#5949be] focus:border-[#5949be] block p-4 transition-colors" 
                               placeholder="Ví dụ: Hội trường A, Tòa nhà Trung tâm">
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="font-semibold text-gray-700 text-sm">Nội dung / Ghi chú</label>
                        <textarea name="description" rows="3" 
                                  class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-[#5949be] focus:border-[#5949be] block p-4 transition-colors" 
                                  placeholder="Mô tả ngắn gọn về nội dung hoặc yêu cầu trang phục...">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2 border-b pb-4">
                    <span class="material-symbols-outlined text-[#5949be]">face_retouching_natural</span>
                    Dữ liệu Khách mời (Huấn luyện AI)
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <div class="space-y-3">
                        <label class="font-semibold text-gray-700 text-sm">1. Danh sách Khách mời (Excel)</label>
                        <label for="excel-file" class="flex flex-col items-center justify-center w-full h-48 border-2 border-[#5949be]/30 border-dashed rounded-2xl cursor-pointer bg-[#5949be]/5 hover:bg-[#5949be]/10 transition-all group">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <span class="material-symbols-outlined text-5xl text-[#5949be] mb-3 group-hover:scale-110 transition-transform">table_view</span>
                                <p class="mb-2 text-sm text-gray-700 font-medium">Tải lên file <span class="text-[#5949be] font-bold">.xlsx, .csv</span></p>
                                <p class="text-xs text-gray-500 text-center px-4">Chứa các cột thông tin và tên file ảnh tương ứng</p>
                            </div>
                            <input id="excel-file" name="file_excel" type="file" class="sr-only" accept=".xlsx, .xls, .csv" required />
                        </label>
                    </div>

                    <div class="space-y-3">
                        <label class="font-semibold text-gray-700 text-sm">2. Thư mục / File Ảnh khuôn mặt</label>
                        <label for="image-files" class="flex flex-col items-center justify-center w-full h-48 border-2 border-[#5949be]/30 border-dashed rounded-2xl cursor-pointer bg-[#5949be]/5 hover:bg-[#5949be]/10 transition-all group">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <span class="material-symbols-outlined text-5xl text-[#5949be] mb-3 group-hover:scale-110 transition-transform">imagesmode</span>
                                <p class="mb-2 text-sm text-gray-700 font-medium">Tải lên nhiều file ảnh <span class="text-[#5949be] font-bold">.jpg, .png</span></p>
                                <p class="text-xs text-gray-500 text-center px-4">Chọn hàng loạt ảnh khuôn mặt của khách mời</p>
                            </div>
                            <input id="image-files" name="file_anh[]" type="file" multiple class="sr-only" accept="image/jpeg, image/png" required />
                        </label>
                    </div>

                </div>

                <div class="mt-6 bg-blue-50/50 border border-blue-100 text-blue-800 p-5 rounded-2xl text-sm flex items-start gap-4 shadow-sm">
                    <span class="material-symbols-outlined text-blue-600 text-2xl mt-0.5">lightbulb</span>
                    <div class="space-y-1">
                        <p class="font-bold text-base">Yêu cầu đồng bộ dữ liệu:</p>
                        <p>Tên file ảnh thực tế tải lên phải trùng khớp hoàn toàn với thông tin khai báo tại cột <code>image_filename</code> trong file Excel danh sách khách mời.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-gray-100 mt-8">
                <a href="{{ route('dashboard') }}" class="px-8 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-full hover:bg-gray-200 transition-colors">
                    Hủy bỏ
                </a>
                <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-[#5949be] to-[#6C5DD3] text-white font-bold rounded-full hover:opacity-90 shadow-[0px_10px_20px_rgba(89,73,190,0.3)] transition-all flex items-center gap-2 hover:scale-[1.02]">
                    <span class="material-symbols-outlined">rocket_launch</span>
                    Khởi tạo Sự kiện & Huấn luyện AI
                </button>
            </div>
            
        </form>
    </div>
</div>

<script>
    document.getElementById('excel-file').addEventListener('change', function(e) {
        if(e.target.files.length > 0) {
            let fileName = e.target.files[0].name;
            this.previousElementSibling.querySelector('p.text-sm').innerHTML = `Đã chọn: <span class="text-[#5949be] font-bold">${fileName}</span>`;
        }
    });

    document.getElementById('image-files').addEventListener('change', function(e) {
        let count = e.target.files.length;
        if(count > 0) {
            this.previousElementSibling.querySelector('p.text-sm').innerHTML = `Đã chọn: <span class="text-[#5949be] font-bold">${count} bức ảnh</span>`;
        }
    });
</script>
@endsection