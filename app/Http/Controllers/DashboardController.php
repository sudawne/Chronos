<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\Guest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Nhận tham số bộ lọc thời gian từ URL (Mặc định là 'month')
        $range = $request->query('range', 'month');
        $tz = 'Asia/Ho_Chi_Minh';
        $now = Carbon::now($tz);

        // 2. Khởi tạo truy vấn Cuộc họp theo thời gian
        $query = Meeting::query();
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('Admin')) {
            $query->where('user_id', $user->id);
        }
        if ($range === 'today') {
            $query->whereDate('start_time', $now->toDateString());
        } elseif ($range === 'week') {
            $query->whereBetween('start_time', [$now->copy()->startOfWeek()->toDateTimeString(), $now->copy()->endOfWeek()->toDateTimeString()]);
        } else { // month
            $query->whereMonth('start_time', $now->month)->whereYear('start_time', $now->year);
        }

        // Lấy danh sách ID các cuộc họp nằm trong bộ lọc
        $meetingIds = $query->pluck('id');

        // 3. Tính toán các Thẻ Thống kê (Cards)
        $totalMeetings = $meetingIds->count();
        $totalGuests = Guest::whereIn('meeting_id', $meetingIds)->count();
        $checkedInGuests = Guest::whereIn('meeting_id', $meetingIds)->where('is_attended', true)->count();
        
        $attendanceRate = $totalGuests > 0 ? round(($checkedInGuests / $totalGuests) * 100, 1) : 0;

        // 4. Lấy Top Địa điểm tổ chức nhiều nhất
        $topLocations = Meeting::whereIn('id', $meetingIds)
            ->select('location', DB::raw('count(*) as total'))
            ->groupBy('location')
            ->orderByDesc('total')
            ->take(3)
            ->get();

        // 5. Lịch trình sắp tới (Lấy 4 sự kiện chưa kết thúc)
        $upcomingMeetings = Meeting::withCount('guests')
            ->where('end_time', '>=', $now->toDateTimeString())
            ->orderBy('start_time', 'asc')
            ->take(4)
            ->get();

        // 6. Dữ liệu thật cho Biểu đồ (Lượt check-in theo khung giờ của hôm nay)
        $chartRawData = Guest::where('is_attended', true)
            ->whereDate('updated_at', clone $now)
            ->select(DB::raw('HOUR(updated_at) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Xây dựng mảng dữ liệu mượt mà từ 07:00 sáng đến 18:00 chiều
        $chartLabels = [];
        $chartData = [];
        for ($i = 7; $i <= 18; $i++) {
            $chartLabels[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $chartData[] = $chartRawData[$i] ?? 0; // Nếu giờ đó ko có ai checkin thì cho bằng 0
        }

        return view('dashboard', compact(
            'totalMeetings', 'totalGuests', 'attendanceRate', 
            'topLocations', 'upcomingMeetings', 'range', 
            'chartLabels', 'chartData', 'now'
        ));
    }
}