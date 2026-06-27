<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class GuestController extends Controller
{
    public function updateFace(Request $request, $id)
    {
        $guest = Guest::findOrFail($id);

        $request->validate([
            'file_anh' => 'required|image|mimes:jpeg,png,jpg|max:5120', 
        ]);

        if ($request->hasFile('file_anh')) {
            $image = $request->file('file_anh');
            
            // Đặt tên file an toàn
            $filename = time() . '_guest_' . $guest->id . '.' . $image->getClientOriginalExtension();
            
            // 1. Xóa ảnh cũ (nếu có) nằm đúng trong ổ đĩa public
            if ($guest->image_filename) {
                $oldPath = 'meetings/' . $guest->meeting_id . '/faces/' . $guest->image_filename;
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }

            // 2. ÉP LƯU VÀO Ổ ĐĨA PUBLIC (Nằm ở storage/app/public/meetings/...)
            $image->storeAs("meetings/{$guest->meeting_id}/faces", $filename, 'public');

            try {
                // Trỏ đường dẫn vật lý để gửi sang Python
                $imagePath = storage_path("app/public/meetings/{$guest->meeting_id}/faces/{$filename}");
                $response = \Illuminate\Support\Facades\Http::timeout(15)->attach(
                    'file', file_get_contents($imagePath), $filename
                )->post('http://localhost:8001/register_face');

                if ($response->successful() && $response['status'] === 'success') {
                    // Ép kiểu mảng Vector thành nhị phân
                    $binaryVector = pack('f*', ...$response['vector']);
                    
                    // Cập nhật Database
                    $guest->update([
                        'image_filename' => $filename,
                        'face_vector' => $binaryVector
                    ]);

                    return back()->with('success', 'Đã trích xuất Vector AI và cập nhật ảnh thành công!');
                } else {
                    return back()->with('error', 'Lỗi AI: ' . ($response['message'] ?? 'Không thể tìm thấy khuôn mặt.'));
                }
            } catch (\Exception $e) {
                return back()->with('error', 'Lỗi kết nối Máy chủ AI (8001).');
            }
        }

        return back()->with('error', 'Vui lòng chọn ảnh!');
    }

    /**
     * Cập nhật thông tin đại biểu (Text + Avatar nếu có)
     */
    public function update(Request $request, $id)
    {
        $guest = Guest::findOrFail($id);

        // Validate dữ liệu đầu vào (Cho phép upload Multipart)
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'position' => 'nullable|string|max:255',
            'seat_location' => 'nullable|string|max:255',
            'file_anh' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // 1. Cập nhật thông tin văn bản trước
        $guest->update([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'position' => $request->position,
            'seat_location' => $request->seat_location,
        ]);

        // 2. NẾU NGƯỜI DÙNG CÓ CHỌN ẢNH MỚI BÊN CỘT PHẢI
        if ($request->hasFile('file_anh')) {
            $image = $request->file('file_anh');
            $filename = time() . '_guest_' . $guest->id . '.' . $image->getClientOriginalExtension();
            
            // Xóa ảnh cũ khỏi ổ cứng (nếu có)
            if ($guest->image_filename) {
                $oldPath = storage_path("app/public/meetings/{$guest->meeting_id}/faces/{$guest->image_filename}");
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Lưu ảnh mới bằng đường dẫn vật lý tuyệt đối (Ép quyền)
            $destinationPath = storage_path("app/public/meetings/{$guest->meeting_id}/faces");
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $image->move($destinationPath, $filename);
            chmod($destinationPath . '/' . $filename, 0644);

            try {
                // Gửi ảnh sang AI Python
                $imagePath = $destinationPath . '/' . $filename;
                $response = \Illuminate\Support\Facades\Http::timeout(15)->attach(
                    'file', file_get_contents($imagePath), $filename
                )->post('http://localhost:8001/register_face');

                if ($response->successful() && $response['status'] === 'success') {
                    $binaryVector = pack('f*', ...$response['vector']);
                    
                    // Cập nhật CSDL
                    $guest->update([
                        'image_filename' => $filename,
                        'face_vector' => $binaryVector
                    ]);

                    return back()->with('success', 'Đã cập nhật thông tin và nạp lại khuôn mặt mới thành công!');
                } else {
                    return back()->with('warning', 'Đã cập nhật thông tin, NHƯNG Lỗi AI: ' . ($response['message'] ?? 'Không trích xuất được khuôn mặt mới.'));
                }
            } catch (\Exception $e) {
                return back()->with('warning', 'Đã cập nhật thông tin, NHƯNG lỗi kết nối Server AI (8001).');
            }
        }

        // 3. Nếu không chọn ảnh mới, chỉ trả về thành công do đã cập nhật Text
        return back()->with('success', 'Đã cập nhật thông tin đại biểu thành công!');
    }

    /**
     * Xóa Đại biểu và dọn dẹp dữ liệu ảnh
     */
    public function destroy($id)
    {
        $guest = Guest::findOrFail($id);
        
        // 1. Dọn dẹp ảnh gốc của khách mời để giải phóng ổ cứng (Nếu có)
        if ($guest->image_filename) {
            // Tùy theo cấu trúc thư mục lưu ảnh của bạn, ví dụ:
            $filePath = storage_path('app/public/meetings/' . $guest->meeting_id . '/faces/' . $guest->image_filename);
            
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        // 2. Xóa bản ghi trong Database
        $guest->delete();

        // Trả về trang cũ với thông báo
        return back()->with('success', 'Đã xóa đại biểu và toàn bộ dữ liệu AI liên quan!');
    }
}
