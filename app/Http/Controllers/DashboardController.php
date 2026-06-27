<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\Guest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->query('range', 'month');
        $tz = 'Asia/Ho_Chi_Minh';
        $now = Carbon::now($tz);

        // ==========================================
        // 1. TRUY VẤN LỌC THỜI GIAN
        // ==========================================
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
        } elseif ($range === 'month') {
            $query->whereMonth('start_time', $now->month)->whereYear('start_time', $now->year);
        } elseif ($range === 'year') {
            $query->whereYear('start_time', $now->year);
        } 

        $meetingIds = $query->pluck('id');

        // ==========================================
        // 2. TÍNH TOÁN KPI TỔNG QUAN & PHỤ
        // ==========================================
        $totalMeetings = $meetingIds->count();
        $totalGuests = Guest::whereIn('meeting_id', $meetingIds)->count();
        $checkedInGuests = Guest::whereIn('meeting_id', $meetingIds)->where('is_attended', true)->count();
        $absentGuests = $totalGuests - $checkedInGuests;
        
        $attendanceRate = $totalGuests > 0 ? round(($checkedInGuests / $totalGuests) * 100, 1) : 0;
        $avgGuests = $totalMeetings > 0 ? round($totalGuests / $totalMeetings) : 0;

        // ==========================================
        // 3. XỬ LÝ BIỂU ĐỒ LƯU LƯỢNG (LINE CHART)
        // ==========================================
        $attendances = Guest::whereIn('meeting_id', $meetingIds)
            ->where('is_attended', true)
            ->whereNotNull('updated_at')
            ->get(['updated_at']);

        $groupedData = [];

        if ($range === 'today') {
            for ($i = 0; $i <= 23; $i++) { $groupedData[str_pad($i, 2, '0', STR_PAD_LEFT) . ':00'] = 0; }
            foreach ($attendances as $att) {
                $hour = Carbon::parse($att->updated_at)->timezone($tz)->format('H:00');
                if (isset($groupedData[$hour])) $groupedData[$hour]++;
            }
        } elseif ($range === 'week') {
            $days = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'CN'];
            foreach ($days as $day) { $groupedData[$day] = 0; }
            foreach ($attendances as $att) {
                $dayIndex = Carbon::parse($att->updated_at)->timezone($tz)->dayOfWeekIso - 1; 
                $groupedData[$days[$dayIndex]]++;
            }
        } elseif ($range === 'month') {
            $daysInMonth = $now->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) { $groupedData["Ngày $i"] = 0; }
            foreach ($attendances as $att) {
                $day = 'Ngày ' . Carbon::parse($att->updated_at)->timezone($tz)->format('j');
                if (isset($groupedData[$day])) $groupedData[$day]++;
            }
        } else {
            for ($i = 1; $i <= 12; $i++) { $groupedData["Tháng $i"] = 0; }
            foreach ($attendances as $att) {
                $month = 'Tháng ' . Carbon::parse($att->updated_at)->timezone($tz)->format('n');
                if (isset($groupedData[$month])) $groupedData[$month]++;
            }
        }

        $chartLabels = array_keys($groupedData);
        $chartData = array_values($groupedData);

        $peakLabel = 'Chưa có';
        $peakValue = 0;
        if (!empty($groupedData) && max($groupedData) > 0) {
            $peakValue = max($groupedData);
            $peakLabel = array_search($peakValue, $groupedData);
        }

        // ==========================================
        // 4. PHÂN TÍCH THEO ĐỊA ĐIỂM (BAR CHART)
        // ==========================================
        $topLocations = Meeting::whereIn('id', $meetingIds)
            ->select('location', DB::raw('count(*) as total'))
            ->groupBy('location')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $locLabels = [];
        $locTotalData = [];
        $locCheckedData = [];

        foreach($topLocations as $loc) {
            $locLabels[] = Str::limit($loc->location, 15);
            $locMeetingIds = Meeting::whereIn('id', $meetingIds)->where('location', $loc->location)->pluck('id');
            
            $locTotalData[] = Guest::whereIn('meeting_id', $locMeetingIds)->count();
            $locCheckedData[] = Guest::whereIn('meeting_id', $locMeetingIds)->where('is_attended', true)->count();
        }

        // ==========================================
        // 5. PHÂN BỔ CHỨC VỤ (TOP POSITIONS)
        // ==========================================
        $topPositions = Guest::whereIn('meeting_id', $meetingIds)
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->select('position', DB::raw('count(*) as total'))
            ->groupBy('position')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // ==========================================
        // 6. BẢNG GIÁM SÁT CHI TIẾT (LỌC THEO KỲ)
        // ==========================================
        $detailedMeetings = Meeting::withCount(['guests as total_guests', 'guests as checked_in_count' => function($q) {
                $q->where('is_attended', true);
            }])
            ->whereIn('id', $meetingIds)
            ->orderBy('start_time', 'desc')
            ->take(6)
            ->get();

        // ==========================================
        // 7. LỊCH TRÌNH SẮP TỚI & DỮ LIỆU CALENDAR
        // ==========================================
        $baseQuery = Meeting::query();
        if (!$user->hasRole('Admin')) {
            $baseQuery->where('user_id', $user->id);
        }

        // Lấy 4 cuộc họp gần nhất sắp/đang diễn ra (Kể cả ngoài kỳ lọc)
        $upcomingMeetings = (clone $baseQuery)
            ->withCount('guests')
            ->where('end_time', '>=', $now->toDateTimeString())
            ->orderBy('start_time', 'asc')
            ->take(4)
            ->get();

        // Lấy dữ liệu Đổ vào FullCalendar
        $calendarEvents = (clone $baseQuery)->get()->map(function($meeting) use ($now) {
            $isPast = Carbon::parse($meeting->end_time)->isPast();
            return [
                'id' => $meeting->id,
                'title' => $meeting->title,
                'start' => Carbon::parse($meeting->start_time)->toIso8601String(),
                'end' => Carbon::parse($meeting->end_time)->toIso8601String(),
                'url' => route('meetings.show', $meeting->id),
                'backgroundColor' => $isPast ? '#cbd5e1' : '#4f46e5', // Xám nếu đã xong, Indigo nếu sắp diễn ra
                'borderColor' => 'transparent'
            ];
        });

        return view('dashboard', compact(
            'totalMeetings', 'totalGuests', 'checkedInGuests', 'absentGuests', 'attendanceRate', 
            'avgGuests', 'peakLabel', 'peakValue',
            'topLocations', 'locLabels', 'locTotalData', 'locCheckedData',
            'topPositions', 'detailedMeetings', 'range', 
            'chartLabels', 'chartData', 'now',
            'upcomingMeetings', 'calendarEvents'
        ));
    }
}