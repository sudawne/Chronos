<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guest;
use Illuminate\Support\Facades\Http;

class GuestController extends Controller
{
    public function updateFace(Request $request, Guest $guest)
    {
        // 1. Kiểm tra file upload hợp lệ
        $request->validate([
            'file_anh' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Tối đa 5MB
        ]);

        $image = $request->file('file_anh');

        try {
            // 2. Bắn bức ảnh sang Python API (Cổng 8001)
            $response = Http::timeout(15)->attach(
                'file', file_get_contents($image->getRealPath()), $image->getClientOriginalName()
            )->post('http://localhost:8001/register_face');

            // 3. Xử lý kết quả trả về từ Python
            if ($response->successful() && $response['status'] === 'success') {
                
                // Lấy mảng 512 chiều và chuyển thành Binary (BLOB)
                $binaryVector = pack('f*', ...$response['vector']);
                
                // Lưu vào database cho vị khách này
                $guest->update([
                    'face_vector' => $binaryVector,
                    // Cập nhật luôn tên file ảnh mới để quản lý nếu cần
                    'image_filename' => $image->getClientOriginalName() 
                ]);
                
                return redirect()->back()->with('success', 'Trích xuất Vector AI thành công cho: ' . $guest->full_name);
            } else {
                // Python trả về lỗi (ví dụ: Không thấy mặt)
                return redirect()->back()->with('error', 'AI Python: ' . ($response['message'] ?? 'Lỗi không xác định.'));
            }
        } catch (\Exception $e) {
            // Lỗi do quên bật Server Python hoặc sập mạng
            return redirect()->back()->with('error', 'Lỗi kết nối: Server AI (Port 8001) có thể đang tắt.');
        }
    }
}
