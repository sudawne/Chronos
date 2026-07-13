@extends('layouts.app')

@section('title', 'Xác thực Khuôn mặt AI | CHRONOS')

@section('content')
<div class="px-4 lg:px-8 pb-12 min-h-screen bg-slate-50/50">
    
    {{-- Header Section --}}
    <div class="mb-8 pt-6">
        <a href="{{ route('meetings.show', $meeting->id) }}" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors mb-4">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Quay lại sự kiện
        </a>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                    <span class="material-symbols-outlined text-indigo-600 text-[32px]">fact_check</span>
                    Kiểm duyệt Ảnh AI
                </h1>
                <p class="text-slate-500 mt-2">Hệ thống AI sẽ tự động phân tích ảnh đại biểu: Yêu cầu <strong class="text-slate-700">chỉ có 1 khuôn mặt duy nhất</strong>, không bị mờ hoặc quá nhỏ.</p>
            </div>
            
            <button id="startValidation" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-xl shadow-sm hover:bg-indigo-700 hover:shadow-md transition-all font-bold active:scale-95 group">
                <span class="material-symbols-outlined group-hover:animate-spin">autorenew</span>
                Bắt đầu Quét Hàng Loạt
            </button>
        </div>
    </div>

    {{-- Bảng Danh sách Đại biểu --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-16 text-center">ID</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Đại biểu</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Ảnh hồ sơ</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Kết quả AI Phân tích</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="guest-table-body">
                    @foreach($guests as $guest)
                        @php
                            // Bỏ qua những người chưa có ảnh vật lý
                            if (!$guest->image_filename) continue;
                            
                            // Nếu đã có face_vector -> Đã quét hợp lệ. Nếu chưa -> Pending
                            $isProcessed = !is_null($guest->face_vector);
                        @endphp
                        
                        <tr id="row-{{ $guest->id }}" class="guest-row hover:bg-slate-50/50 transition-colors {{ $isProcessed ? 'processed' : 'pending' }}" data-guest-id="{{ $guest->id }}">
                            <td class="px-6 py-4 text-center text-sm font-medium text-slate-400">
                                #{{ $guest->id }}
                            </td>
                            
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">{{ $guest->full_name }}</div>
                                <div class="text-xs text-slate-500">{{ $guest->position ?? 'Đại biểu' }}</div>
                            </td>
                            
                            <td class="px-6 py-4 text-center">
                                <div class="inline-block w-14 h-14 rounded-xl border border-slate-200 shadow-sm overflow-hidden bg-slate-100">
                                    <img src="{{ asset('storage/meetings/'.$meeting->id.'/faces/'.$guest->image_filename) }}" class="w-full h-full object-cover" alt="Ảnh">
                                </div>
                            </td>
                            
                            {{-- CỘT TRẠNG THÁI AI --}}
                            <td class="px-6 py-4 status-cell">
                                @if($isProcessed)
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm font-bold">
                                        <span class="material-symbols-outlined text-[18px]">verified</span> Hợp lệ (Đã trích xuất)
                                    </div>
                                @else
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 border border-slate-200 text-slate-500 rounded-lg text-sm font-medium">
                                        <span class="material-symbols-outlined text-[18px]">hourglass_empty</span> Đang chờ lệnh quét...
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('startValidation').addEventListener('click', async function() {
    const btn = this;
    btn.disabled = true; 
    btn.innerHTML = `<span class="material-symbols-outlined animate-spin">model_training</span> Đang xử lý AI...`;
    btn.classList.replace('bg-indigo-600', 'bg-slate-400');

    const rows = document.querySelectorAll('.guest-row');
    const meetingId = {{ $meeting->id }};
    
    let successCount = 0;
    let errorCount = 0;

    for (let i = 0; i < rows.length; i++) {
        let row = rows[i];
        let guestId = row.getAttribute('data-guest-id');
        let statusCell = row.querySelector('.status-cell');

        // Hiển thị trạng thái đang gửi cho dòng hiện tại
        statusCell.innerHTML = `
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 border border-blue-200 text-blue-600 rounded-lg text-sm font-bold">
                <span class="material-symbols-outlined text-[18px] animate-spin">settings_b_roll</span> Đang phân tích...
            </div>
        `;

        try {
            let response = await fetch(`/meetings/${meetingId}/process-validation`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ guest_id: guestId })
            });

            let result = await response.json();
            
            // XỬ LÝ GIAO DIỆN DỰA VÀO CÂU TRẢ LỜI CỦA PYTHON AI
            if (result.status === 'success') {
                statusCell.innerHTML = `
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm font-bold">
                        <span class="material-symbols-outlined text-[18px]">verified</span> Hợp lệ (1 Khuôn mặt)
                    </div>
                `;
                row.classList.remove('pending');
                row.classList.add('processed');
                successCount++;
            } else {
                let errorMsg = result.message || 'Lỗi không xác định';
                let errorIcon = 'error';
                
                // Nhận diện lỗi 0 mặt hoặc >1 mặt từ Python trả về
                if (errorMsg.includes('Không phát hiện')) {
                    errorIcon = 'person_off';
                    errorMsg = 'Lỗi: Không có mặt';
                } else if (errorMsg.includes('Phát hiện')) {
                    errorIcon = 'groups';
                    errorMsg = 'Lỗi: Quá nhiều mặt';
                } else if (errorMsg.includes('nhỏ hoặc mờ')) {
                    errorIcon = 'blur_on';
                    errorMsg = 'Lỗi: Mặt quá nhỏ/mờ';
                }

                statusCell.innerHTML = `
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-rose-50 border border-rose-200 text-rose-600 rounded-lg text-sm font-bold">
                        <span class="material-symbols-outlined text-[18px]">${errorIcon}</span> ${errorMsg}
                    </div>
                `;
                errorCount++;
            }

        } catch (error) {
            statusCell.innerHTML = `
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 border border-slate-300 text-slate-600 rounded-lg text-sm font-bold">
                    <span class="material-symbols-outlined text-[18px]">wifi_off</span> Mất kết nối AI Server
                </div>
            `;
            errorCount++;
        }
    }
    
    // Khôi phục nút bấm sau khi chạy xong
    btn.disabled = false;
    btn.innerHTML = `<span class="material-symbols-outlined">done_all</span> Quét hoàn tất (${successCount} Tốt / ${errorCount} Lỗi)`;
    btn.classList.replace('bg-slate-400', 'bg-indigo-600');
    
    Swal.fire({
        title: 'Hoàn tất quét AI!',
        html: `Đã xử lý xong.<br>Hợp lệ: <b>${successCount}</b> - Không hợp lệ: <b>${errorCount}</b>`,
        icon: errorCount > 0 ? 'warning' : 'success',
        confirmButtonColor: '#4f46e5'
    });
});
</script>
@endsection