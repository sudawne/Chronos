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
        $eventId = $request->input('event_id');
        $guestId = $request->input('guest_id');

        $event = Event::find($eventId);
        $guest = Guest::find($guestId);

        if (!$event || !$guest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ. Không tìm thấy Sự kiện hoặc Đại biểu!'
            ], 404);
        }

        $alreadyCheckedIn = Attendance::where('event_id', $eventId)
                                      ->where('guest_id', $guestId)
                                      ->first();
        
        if ($alreadyCheckedIn) {
             return response()->json([
                'status' => 'warning',
                'message' => 'Đại biểu ' . $guest->name . ' đã được ghi nhận trước đó.',
                'time' => Carbon::parse($alreadyCheckedIn->check_in_time)->format('H:i:s')
            ], 200);
        }

        $newRecord = Attendance::create([
            'event_id' => $eventId,
            'guest_id' => $guestId,
            'check_in_time' => Carbon::now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Xin chào ' . $guest->name . ', điểm danh thành công!',
            'data' => [
                'name' => $guest->name,
                'check_in_at' => $newRecord->check_in_time->format('H:i:s d/m/Y')
            ]
        ], 200);
    }
}