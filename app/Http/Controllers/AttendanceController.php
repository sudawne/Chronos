<?php 
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Attendance;
use Carbon\Carbon;

class CheckInController extends Controller
{
    public function submitCheckIn(Request $request)
    {
        // 1. Nhận dữ liệu từ Python gửi sang
        $eventId = $request->input('event_id');
        $guestId = $request->input('guest_id');

        // 2. Kiểm tra xem sự kiện và khách mời có tồn tại không
        $event = Event::find($eventId);
        $guest = Guest::find($guestId);

        if (!$event || !$guest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sự kiện hoặc Khách mời không hợp lệ!'
            ], 404);
        }

        // 3. (Tùy chọn) Kiểm tra xem khách này đã điểm danh chưa để tránh spam
        $alreadyCheckedIn = Attendance::where('event_id', $eventId)
                                      ->where('guest_id', $guestId)
                                      ->first();
        
        if ($alreadyCheckedIn) {
             return response()->json([
                'status' => 'warning',
                'message' => 'Đại biểu ' . $guest->name . ' đã điểm danh trước đó rồi!'
            ], 200);
        }

        // 4. Ghi nhận vào sổ điểm danh (Database)
        Attendance::create([
            'event_id' => $eventId,
            'guest_id' => $guestId,
            'check_in_time' => Carbon::now(), // Lấy giờ hiện tại
        ]);

        // 5. Trả lời lại cho Python biết là thành công
        return response()->json([
            'status' => 'success',
            'message' => 'Xin chào ' . $guest->name . ', check-in thành công!',
            'check_in_time' => Carbon::now()->format('H:i:s d-m-Y')
        ], 200);
    }
}