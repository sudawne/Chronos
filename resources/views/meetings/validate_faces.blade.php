@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Xác thực khuôn mặt cho sự kiện: {{ $meeting->title }}</h2>
    <p>Hệ thống sẽ gửi ảnh sang AI để trích xuất đặc trưng khuôn mặt.</p>

    <button id="startValidation" class="btn btn-primary mb-3">Bắt đầu xác thực hàng loạt</button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên Khách</th>
                <th>Ảnh gốc</th>
                <th>Trạng thái AI</th>
            </tr>
        </thead>
        <tbody>
            @foreach($guests as $guest)
            <tr id="row-{{ $guest->id }}" class="guest-row" data-guest-id="{{ $guest->id }}">
                <td>{{ $guest->id }}</td>
                <td>{{ $guest->full_name }}</td>
                <td>
                    <img src="{{ asset('storage/meetings/'.$meeting->id.'/faces/'.$guest->image_filename) }}" width="50" height="50" alt="Ảnh">
                </td>
                <!-- Thêm class status-cell vào cột trạng thái -->
                <td class="status-cell text-gray-500">
                    <span class="spinner-border spinner-border-sm" role="status"></span> Đang chờ xử lý...
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script>
document.getElementById('startValidation').addEventListener('click', async function() {
    this.disabled = true; // Khóa nút
    const rows = document.querySelectorAll('.guest-row');
    const meetingId = {{ $meeting->id }};
    
    for (let i = 0; i < rows.length; i++) {
        let row = rows[i];
        let guestId = row.getAttribute('data-guest-id');
        let statusCell = row.querySelector('.status-cell');

        statusCell.innerHTML = '⏳ Đang gửi dữ liệu...';

        try {
            let response = await fetch(`/meetings/${meetingId}/process-validation`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json', // ÉP LARAVEL TRẢ VỀ JSON NẾU CÓ LỖI
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ guest_id: guestId })
            });

            // Nếu Laravel báo lỗi (404, 500, 419...)
            if (!response.ok) {
                let errorData = await response.json();
                console.error("Lỗi từ Laravel:", errorData);
                statusCell.innerHTML = `<span style="color: red; font-weight: bold;">❌ Lỗi: ${errorData.message || 'Server Error'}</span>`;
                continue; // Bỏ qua người này, chạy tiếp người sau
            }

            // Nếu thành công 200 OK
            let result = await response.json();
            
            if (result.status === 'success') {
                statusCell.innerHTML = `<span style="color: green; font-weight: bold;">✅ Thành công</span>`;
            } else {
                statusCell.innerHTML = `<span style="color: red; font-weight: bold;">❌ ${result.message}</span>`;
            }
        } catch (error) {
            statusCell.innerHTML = `<span style="color: red;">❌ Mất kết nối! Xem F12</span>`;
            console.error("Chi tiết lỗi:", error);
        }
    }
    
    this.disabled = false; // Mở khóa nút sau khi chạy xong
    alert('Quá trình kiểm tra đã hoàn tất!');
});
</script>
@endsection