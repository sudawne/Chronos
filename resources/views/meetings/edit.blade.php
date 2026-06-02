@extends('layouts.app')

@section('title', 'Chỉnh Sửa Cuộc Họp | AI Attendance')
@section('page_title', 'Chỉnh sửa cuộc họp')
@section('page_subtitle', 'Cập nhật lại các tham số thời gian, địa điểm hoặc cấu hình ngưỡng AI')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-8 md:p-12">
        <form action="{{ route('meetings.update', $meeting->id) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
                    <ul class="list-disc list-inside text-sm text-red-700 font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2 md:col-span-2">
                    <label class="font-semibold text-gray-700 text-sm">Tên cuộc họp / Sự kiện <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $meeting->title) }}" required 
                           class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-[#5949be] focus:border-[#5949be] block p-4 transition-colors">
                </div>

                <div class="space-y-2">
                    <label class="font-semibold text-gray-700 text-sm">Thời gian bắt đầu <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="start_time" value="{{ old('start_time', date('Y-m-d\TH:i', strtotime($meeting->start_time))) }}" required 
                           class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-[#5949be] focus:border-[#5949be] block p-4 transition-colors">
                </div>

                <div class="space-y-2">
                    <label class="font-semibold text-gray-700 text-sm">Thời gian kết thúc <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="end_time" value="{{ old('end_time', date('Y-m-d\TH:i', strtotime($meeting->end_time))) }}" required 
                           class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-[#5949be] focus:border-[#5949be] block p-4 transition-colors">
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label class="font-semibold text-gray-700 text-sm">Địa điểm tổ chức <span class="text-red-500">*</span></label>
                    <input type="text" name="location" value="{{ old('location', $meeting->location) }}" required 
                           class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-[#5949be] focus:border-[#5949be] block p-4 transition-colors">
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label class="font-semibold text-gray-700 text-sm">Ngưỡng nhận diện ArcFace (Cosine)</label>
                    <input type="number" step="0.01" name="recognition_threshold" value="{{ old('recognition_threshold', $meeting->recognition_threshold) }}" 
                           class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-[#5949be] focus:border-[#5949be] block p-4 transition-colors">
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label class="font-semibold text-gray-700 text-sm">Nội dung / Ghi chú</label>
                    <textarea name="description" rows="3" 
                              class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:ring-[#5949be] focus:border-[#5949be] block p-4 transition-colors">{{ old('description', $meeting->description) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t border-gray-100">
                <a href="{{ route('meetings.index') }}" class="px-8 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-full hover:bg-gray-200 transition-colors">
                    Hủy bỏ
                </a>
                <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-[#5949be] to-[#6C5DD3] text-white font-bold rounded-full hover:opacity-90 shadow-md transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">save</span>
                    Cập nhật thay đổi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection