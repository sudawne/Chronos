<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\Guest;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory; 
use Illuminate\Support\Facades\Http;
use App\Mail\GuestTicketMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Notifications\SystemAlert;
use Illuminate\Support\Facades\Notification;

class MeetingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'required|string|max:255',
            'file_excel' => 'required|file|mimes:xlsx,xls,csv,txt',
            'file_anh' => 'required',
        ]);

        $meeting = Meeting::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'description' => $request->description, 
            'recognition_threshold' => $request->recognition_threshold ?? 0.55,
        ]);

        if ($request->hasFile('file_excel')) {
            $file = $request->file('file_excel');
            try {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                if (count($rows) > 0) {
                    unset($rows[0]);
                }

                foreach ($rows as $row) {
                    if (empty($row[0])) continue;

                    Guest::create([
                        'meeting_id'     => $meeting->id,
                        'full_name'      => trim($row[0]),
                        'email'          => trim($row[1]) ?? null,
                        'position'       => trim($row[2]) ?? null,
                        'seat_location'  => trim($row[3]) ?? null,
                        'image_filename' => trim($row[4]) ?? null,
                        'face_vector'    => null, 
                        'is_attended'    => false,
                    ]);
                }
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['file_excel' => 'Lỗi đọc Excel: ' . $e->getMessage()]);
            }
        }
        
        $successCount = 0;
        $errorCount = 0;

        if ($request->hasFile('file_anh')) {
            $folderPath = "meetings/{$meeting->id}/faces";

            foreach ($request->file('file_anh') as $image) {
                $filename = $image->getClientOriginalName();
                $image->storeAs($folderPath, $filename, 'public');

                $guest = Guest::where('meeting_id', $meeting->id)->where('image_filename', $filename)->first();

                if ($guest) {
                    try {
                        $response = Http::timeout(10)->attach(
                            'file', file_get_contents($image->getRealPath()), $filename
                        )->post('http://localhost:8001/register_face');

                        if ($response->successful() && $response['status'] === 'success') {
                            $vectorArray = $response['vector'];
                            $binaryVector = pack('f*', ...$vectorArray);
                            
                            $guest->update(['face_vector' => $binaryVector]);
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                    } catch (\Exception $e) {
                        $errorCount++;
                    }
                }
            }
        }

        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user) {
        Notification::send($user, new SystemAlert([
            'title'      => 'Tạo sự kiện thành công!',
            'message'    => 'Cuộc họp đã được lên lịch thành công.',
            'icon'       => 'event_available',
            'bg_color'   => 'bg-emerald-500',
            'text_color' => 'text-emerald-600 dark:text-emerald-400',
            'link'       => route('meetings.index') 
        ]));
}

        $msg = "Khởi tạo thành công! Đã nạp AI cho $successCount khách. Lỗi: $errorCount.";
        return redirect()->route('dashboard')->with('success', $msg);
    }

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            $meetings = Meeting::with('user')->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $meetings = Meeting::where('user_id', Auth::id())->orderBy('created_at', 'desc')->paginate(10);
        }
        return view('meetings.index', compact('meetings'));
    }

    // Xem chi tiết cuộc họp và danh sách thành viên
    public function show(Meeting $meeting)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('Admin') && $meeting->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền quản lý cuộc họp này!');
        }
        $meeting->load('guests'); 
        return view('meetings.show', compact('meeting'));
    }

    // Hiển thị form chỉnh sửa cuộc họp
    public function edit(Meeting $meeting)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('Admin') && $meeting->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền quản lý cuộc họp này!');
        }
        return view('meetings.edit', compact('meeting'));
    }

    // Cập nhật thông tin cuộc họp
    public function update(Request $request, Meeting $meeting)
    {
        $this->authorizeMeetingAction($meeting, 'meeting.edit');
        $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'required|string|max:255',
        ]);

        $meeting->update([
            'title' => $request->title,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'description' => $request->description,
            'recognition_threshold' => $request->recognition_threshold ?? 0.55,
        ]);

        $user = Auth::user();
        Notification::send($user, new SystemAlert([
            'title'      => 'Đã cập nhật sự kiện',
            'message'    => 'Thông tin cuộc họp "' . $meeting->title . '" vừa được thay đổi.',
            'icon'       => 'edit_calendar',
            'bg_color'   => 'bg-blue-500',
            'text_color' => 'text-blue-600 dark:text-blue-400',
            'link'       => route('meetings.show', $meeting->id)
        ]));

        return redirect()->route('meetings.index')->with('success', 'Cập nhật thông tin cuộc họp thành công!');
    }

    // Xóa cuộc họp 
    public function destroy(Meeting $meeting)
    {
        $this->authorizeMeetingAction($meeting, 'meeting.delete');
        $meeting->delete();
        return redirect()->route('meetings.index')->with('success', 'Đã xóa cuộc họp thành công!');
    }

    // Hàm mở trang Màn hình Chào mừng
    public function welcomeScreen(Meeting $meeting)
    {
        return view('meetings.welcome', compact('meeting'));
    }

    public function latestCheckin(Meeting $meeting)
    {
        $latestGuest = Guest::where('meeting_id', $meeting->id)
            ->where('is_attended', true)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($latestGuest && $latestGuest->updated_at->diffInSeconds(now()) <= 6) {
            
            $fileName = "live_face_{$latestGuest->id}.jpg";
            $liveImagePath = "meetings/{$meeting->id}/live_faces/{$fileName}";

            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($liveImagePath)) {
                $imageUrl = asset("storage/" . $liveImagePath) . '?t=' . time();
            } else {
                $imageUrl = 'https://ui-avatars.com/api/?name='.urlencode($latestGuest->full_name).'&size=256&background=e2e8f0';
            }

            return response()->json([
                'status' => 'found',
                'guest' => [
                    'name' => $latestGuest->full_name,
                    'position' => $latestGuest->position ?? 'Đại biểu',
                    'seat' => $latestGuest->seat_location ?? '',
                    'avatar' => $imageUrl
                ]
            ]);
        }

        return response()->json(['status' => 'waiting']);
    }

    //Khôgn sài nữa
    // public function startCamera(Meeting $meeting)
    // {
    //     $aiFolder = 'D:\KLTN\AI'; 
        
    //     $pythonScript = 'diem_danh_live.py'; 

    //     $pythonExecutable = 'C:\Users\Zbook\AppData\Local\Programs\Python\Python311\python.exe'; 

    //     $command = 'cd /d "' . $aiFolder . '" && start cmd /k ""' . $pythonExecutable . '" "' . $pythonScript . '" ' . $meeting->id . '"';

    //     pclose(popen($command, "r"));

    //     return redirect()->back()->with('success', 'Đang nạp model AI và khởi động Camera... Vui lòng đợi cửa sổ Terminal bật lên!');
    // }

    public function onlineCheckin(Request $request, Meeting $meeting)
    {
        $gateName = $request->query('gate', 'Cổng Phụ'); 
        return view('meetings.online', compact('meeting', 'gateName'));
    }

    public function gateHeartbeat(Request $request, Meeting $meeting)
    {
        $gateName = $request->input('gate_name');
        $cacheKey = 'meeting_' . $meeting->id . '_active_gates';
        
        $gates = Cache::get($cacheKey, []);
        $gates[$gateName] = time(); 
        
        Cache::put($cacheKey, $gates, 60);
        
        return response()->json(['status' => 'ok']);
    }

    // Trả về danh sách cổng cho màn hình Quản lý
    public function getActiveGates(Meeting $meeting)
    {
        $cacheKey = 'meeting_' . $meeting->id . '_active_gates';
        $gates = Cache::get($cacheKey, []);
        $activeGates = [];

        foreach ($gates as $name => $timestamp) {
            if (time() - $timestamp <= 8) {
                $activeGates[] = $name;
            }
        }

        return response()->json(['active_gates' => $activeGates]);
    }
    public function startApiServer()
    {
        $aiFolder = 'D:\KLTN\AI'; 

        $pythonExecutable = 'C:\Users\Zbook\AppData\Local\Programs\Python\Python311\python.exe'; 

        $command = 'cd /d "' . $aiFolder . '" && start cmd /k ""' . $pythonExecutable . '" -m uvicorn api_ai:app --host 0.0.0.0 --port 8001"';

        pclose(popen($command, "r"));

        return redirect()->back()->with('success', 'Đã khởi động Máy chủ AI API thành công! Bạn có thể bắt đầu điểm danh.');
    }

    // Gửi mail vé mời cho khách tham dự 
    public function sendTickets(Meeting $meeting)
    {
        $guests = $meeting->guests()->whereNotNull('email')->get();
        $count = 0;

        foreach ($guests as $guest) {
            try {
                Mail::to($guest->email)->send(new GuestTicketMail($guest, $meeting));
                $count++;
            } catch (Exception $e) {
                Log::error("Lỗi gửi mail cho " . $guest->email . ": " . $e->getMessage());
            }
        }

        $user = Auth::user();
        Notification::send($user, new SystemAlert([
            'title'      => 'Phân phối vé hoàn tất',
            'message'    => "Hệ thống đã gửi thành công $count vé mời QR cho sự kiện " . $meeting->title,
            'icon'       => 'mark_email_read',
            'bg_color'   => 'bg-emerald-500',
            'text_color' => 'text-emerald-600 dark:text-emerald-400',
            'link'       => route('meetings.show', $meeting->id)
        ]));

        return redirect()->back()->with('success', "Đã gửi thành công $count vé mời QR Code qua Email!");
    }

    // Gửi mail yêu cầu cung cấp ảnh cho những người còn thiếu
    public function sendPhotoRequests(Meeting $meeting)
    {
        $guests = $meeting->guests()
            ->whereNotNull('email')
            ->whereNull('face_vector')
            ->get();
            
        $count = 0;

        foreach ($guests as $guest) {
            try {
                // Tạo link bảo mật 
                $secureUrl = \Illuminate\Support\Facades\URL::signedRoute('guest.photo.form', [
                    'meeting' => $meeting->id, 
                    'guest'   => $guest->id
                ]);

                Mail::to($guest->email)->send(new \App\Mail\RequestPhotoMail($guest, $meeting, $secureUrl));
                $count++;
            } catch (\Exception $e) {
                Log::error("Lỗi gửi mail yêu cầu ảnh cho " . $guest->email . ": " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Đã gửi thành công $count email yêu cầu cập nhật ảnh khuôn mặt!");
    }

    // Trả về giao diện Quét QR
    public function scanQr(Meeting $meeting)
    {
        return view('meetings.scan_qr', compact('meeting'));
    }

    // Nhận dữ liệu JSON từ Camera quét được và cập nhật CSDL
    public function processQrScan(Request $request)
    {
        $data = $request->validate([
            'm' => 'required|integer', // Mã ID cuộc họp
            'g' => 'required|integer', // Mã ID đại biểu
        ]);

        // Tìm khách mời dựa trên dữ liệu quét
        $guest = Guest::where('id', $data['g'])
                      ->where('meeting_id', $data['m'])
                      ->first();

        // Xử lý các tình huống
        if (!$guest) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Mã QR không hợp lệ hoặc vé không thuộc sự kiện này!'
            ]);
        }

        if ($guest->is_attended) {
            return response()->json([
                'status' => 'warning', 
                'message' => 'Đại biểu ' . $guest->full_name . ' đã điểm danh rồi!'
            ]);
        }

        // Đánh dấu đã điểm danh
        $guest->update(['is_attended' => true]);

        return response()->json([
            'status' => 'success',
            'name' => $guest->full_name,
            'position' => $guest->position ?? 'Đại biểu',
            'message' => 'Check-in thành công!'
        ]);
    }

    public function addGuest(Request $request, Meeting $meeting)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'position' => 'nullable|string|max:255',
            'seat_location' => 'nullable|string|max:255',
            'file_anh' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $guest = Guest::create([
            'meeting_id' => $meeting->id,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'position' => $request->position,
            'seat_location' => $request->seat_location,
            'is_attended' => false,
        ]);

        if ($request->hasFile('file_anh')) {
            $image = $request->file('file_anh');
            
            $filename = time() . '_guest_' . $guest->id . '.' . $image->getClientOriginalExtension();
            
            $image->storeAs("meetings/{$meeting->id}/faces", $filename, 'public');

            $guest->update(['image_filename' => $filename]);

            try {
                $imagePath = storage_path("app/public/meetings/{$meeting->id}/faces/{$filename}");
                
                $response = \Illuminate\Support\Facades\Http::timeout(15)->attach(
                    'file', file_get_contents($imagePath), $filename
                )->post('http://localhost:8001/register_face');

                if ($response->successful() && $response['status'] === 'success') {
                    // Chuyển mảng float sang Binary BLOB
                    $binaryVector = pack('f*', ...$response['vector']);
                    $guest->update(['face_vector' => $binaryVector]);
                    
                    $user = Auth::user();
                    Notification::send($user, new SystemAlert([
                        'title'      => 'Thêm đại biểu thành công',
                        'message'    => 'Đã thêm đại biểu ' . $guest->full_name . ' vào sự kiện ' . $meeting->title,
                        'icon'       => 'person_add',
                        'bg_color'   => 'bg-indigo-500',
                        'text_color' => 'text-indigo-600 dark:text-indigo-400',
                        'link'       => route('meetings.show', $meeting->id)
                    ]));

                    return redirect()->back()->with('success', 'Đã thêm đại biểu ' . $guest->full_name . ' và nạp khuôn mặt thành công!');
                } else {
                    return redirect()->back()->with('warning', 'Đã lưu đại biểu & ảnh, NHƯNG lỗi AI: ' . ($response['message'] ?? 'Không trích xuất được khuôn mặt.'));
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('warning', 'Đã lưu đại biểu & ảnh, NHƯNG lỗi kết nối Server AI (Port 8001 đang tắt).');
            }
        }

        return redirect()->back()->with('success', 'Đã thêm đại biểu ' . $guest->full_name . ' thành công! Vui lòng cập nhật ảnh nhận diện sau.');
    }

    //Hiển thị form cho khách hàng tự chụp ảnh
    public function guestPhotoForm(Request $request, Meeting $meeting, Guest $guest)
    {
        if ($guest->meeting_id !== $meeting->id) abort(404);
        
        return view('guests.upload_photo', compact('meeting', 'guest'));
    }

    //Nhận ảnh từ khách, lưu và nạp vào AI
    public function guestPhotoUpload(Request $request, Meeting $meeting, Guest $guest)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', 
        ]);

        $image = $request->file('photo');
        $filename = time() . '_guest_' . $guest->id . '.' . $image->getClientOriginalExtension();
        
        $image->storeAs("meetings/{$meeting->id}/faces", $filename, 'public');
        $guest->update(['image_filename' => $filename]);

        try {
            $imagePath = storage_path("app/public/meetings/{$meeting->id}/faces/{$filename}");
            $response = \Illuminate\Support\Facades\Http::timeout(15)->attach(
                'file', file_get_contents($imagePath), $filename
            )->post('http://localhost:8001/register_face');

            if ($response->successful() && $response['status'] === 'success') {
                $binaryVector = pack('f*', ...$response['vector']);
                $guest->update(['face_vector' => $binaryVector]);

                $owner = \App\Models\User::find($meeting->user_id);
                if ($owner) {
                    Notification::send($owner, new SystemAlert([
                        'title'      => 'Đại biểu nạp ảnh thành công',
                        'message'    => $guest->full_name . ' vừa tự cập nhật dữ liệu khuôn mặt qua link bảo mật.',
                        'icon'       => 'face_retouching_natural',
                        'bg_color'   => 'bg-emerald-500',
                        'text_color' => 'text-emerald-600 dark:text-emerald-400',
                        'link'       => route('meetings.show', $meeting->id)
                    ]));
                }
                
                return back()->with('success', 'Khuôn mặt của bạn đã được AI ghi nhận thành công.');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Lỗi AI khi khách tự up ảnh: " . $e->getMessage());
        }

        return back()->with('error', 'Không thể nhận diện khuôn mặt. Vui lòng chụp lại ảnh khác sáng và rõ nét hơn.');
    }

    public function globalSearch(Request $request)
    {
        $query = $request->query('query');
        
        if (empty($query)) {
            return response()->json(['meetings' => [], 'guests' => []]);
        }

        $meetings = \App\Models\Meeting::where('title', 'LIKE', '%' . $query . '%')
            ->orWhere('location', 'LIKE', '%' . $query . '%')
            ->select('id', 'title', 'location')
            ->take(5)
            ->get();

        $guests = \App\Models\Guest::where('full_name', 'LIKE', '%' . $query . '%')
            ->orWhere('email', 'LIKE', '%' . $query . '%')
            ->join('meetings', 'guests.meeting_id', '=', 'meetings.id')
            ->select('guests.id', 'guests.full_name', 'guests.email', 'guests.meeting_id', 'meetings.title as meeting_title')
            ->take(5)
            ->get();

        return response()->json([
            'meetings' => $meetings,
            'guests' => $guests
        ]);
    }
    
    public function updateWelcomeConfig(Request $request, Meeting $meeting)
    {
        $config = $meeting->welcome_config ? json_decode($meeting->welcome_config, true) : [];

        if ($request->hasFile('bg_image')) {
            $path = $request->file('bg_image')->store("meetings/{$meeting->id}/welcome", 'public');
            $config['bg_image'] = '/storage/' . $path;
        }
        
        if ($request->hasFile('logo_image')) {
            $path = $request->file('logo_image')->store("meetings/{$meeting->id}/welcome", 'public');
            $config['logo_image'] = '/storage/' . $path;
        }

        $config['name_color'] = $request->input('name_color', '#ffffff');
        $config['name_size'] = $request->input('name_size', '3rem');
        $config['text_align'] = $request->input('text_align', 'center');
        $config['box_position_y'] = $request->input('box_position_y', 'center'); // top, center, bottom
        
        $meeting->update([
            'welcome_config' => json_encode($config)
        ]);

        return back()->with('success', 'Đã cập nhật giao diện màn hình chào mừng thành công!');
    }

    // Trả về giao diện Designer
    public function designer(Meeting $meeting)
    {
        // Trích xuất cấu hình cũ 
        $config = $meeting->welcome_config ? json_decode($meeting->welcome_config, true) : null;
        return view('meetings.designer', compact('meeting', 'config'));
    }

    // Lưu design
    public function saveDesign(Request $request, Meeting $meeting)
    {
        $payload = $request->validate([
            'bg_image' => 'nullable|string',
            'bg_color' => 'nullable|string',
            'elements' => 'required|array'
        ]);

        $config = [
            'bg_color' => $payload['bg_color'] ?? '#0f172a',
            'elements' => $payload['elements']
        ];

        // 1. TÁCH BASE64 THÀNH FILE ẢNH NỀN 
        if (!empty($payload['bg_image']) && str_starts_with($payload['bg_image'], 'data:image')) {
            $image_parts = explode(";base64,", $payload['bg_image']);
            $image_base64 = base64_decode($image_parts[1]);
            
            $fileName = 'bg_' . time() . '.png';
            $path = "meetings/{$meeting->id}/welcome/{$fileName}";
            
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $image_base64);
            $config['bg_image'] = '/storage/' . $path; 
        } else {
            $config['bg_image'] = $payload['bg_image'];
        }

        // 2. TÁCH BASE64 THÀNH FILE ẢNH CHO CÁC LOGO/HÌNH ẢNH NHỎ
        foreach ($config['elements'] as &$el) {
            if (isset($el['type']) && $el['type'] === 'image' && isset($el['src'])) {
                
                if (str_starts_with($el['src'], 'data:image')) {
                    $image_parts = explode(";base64,", $el['src']);
                    $image_base64 = base64_decode($image_parts[1]);
                    
                    $fileName = 'element_' . uniqid() . '.png';
                    $path = "meetings/{$meeting->id}/welcome/{$fileName}";
                    
                    \Illuminate\Support\Facades\Storage::disk('public')->put($path, $image_base64);
                    
                    $el['src'] = '/storage/' . $path; 
                }
            }
        }
        $meeting->update([
            'welcome_config' => json_encode($config)
        ]);

        return response()->json(['status' => 'success', 'message' => 'Đã lưu thiết kế!']);
    }

    // Mở trang Mini Game 
    public function game(Meeting $meeting)
    {
        return view('meetings.game', compact('meeting'));
    }

    //Xử lý design template
    // API Lấy danh sách Template
    public function getTemplates()
    {
        $templates = \App\Models\WelcomeTemplate::orderBy('created_at', 'desc')->get();
        return response()->json($templates);
    }

    // API Lưu bản thiết kế thành Template mới
    public function saveTemplate(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'config' => 'required|array'
        ]);

        $configData = $payload['config'];

        // ==========================================
        // 1. XỬ LÝ ẢNH NỀN (BACKGROUND)
        // ==========================================
        if (!empty($configData['bg_image'])) {
            if (str_starts_with($configData['bg_image'], 'data:image')) {
                // Trường hợp 1: Ảnh mới upload (Base64)
                $image_parts = explode(";base64,", $configData['bg_image']);
                $image_base64 = base64_decode($image_parts[1]);
                
                $fileName = 'tpl_bg_' . time() . '.png';
                $path = "templates/{$fileName}"; 
                
                \Illuminate\Support\Facades\Storage::disk('public')->put($path, $image_base64);
                $configData['bg_image'] = '/storage/' . $path;
            } elseif (str_contains($configData['bg_image'], '/storage/meetings/')) {
                // Trường hợp 2: Ảnh đang lấy từ một Meeting có sẵn
                $oldPath = str_replace('/storage/', '', parse_url($configData['bg_image'], PHP_URL_PATH));
                
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                    $fileName = 'tpl_bg_' . time() . '.png';
                    $newPath = "templates/{$fileName}";
                    \Illuminate\Support\Facades\Storage::disk('public')->copy($oldPath, $newPath);
                    $configData['bg_image'] = '/storage/' . $newPath;
                }
            }
        }

        // ==========================================
        // 2. XỬ LÝ LOGO / HÌNH ẢNH CON
        // ==========================================
        if (!empty($configData['elements'])) {
            foreach ($configData['elements'] as &$el) {
                if (isset($el['type']) && $el['type'] === 'image' && !empty($el['src'])) {
                    if (str_starts_with($el['src'], 'data:image')) {
                        // Trường hợp 1: Ảnh con mới upload (Base64)
                        $image_parts = explode(";base64,", $el['src']);
                        $image_base64 = base64_decode($image_parts[1]);
                        
                        $fileName = 'tpl_element_' . uniqid() . '.png';
                        $path = "templates/{$fileName}";
                        
                        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $image_base64);
                        $el['src'] = '/storage/' . $path; 
                    } elseif (str_contains($el['src'], '/storage/meetings/')) {
                        // Trường hợp 2: Ảnh con đang lấy từ một Meeting có sẵn 
                        $oldPath = str_replace('/storage/', '', parse_url($el['src'], PHP_URL_PATH));
                        
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                            $fileName = 'tpl_element_' . uniqid() . '.png';
                            $newPath = "templates/{$fileName}";
                            \Illuminate\Support\Facades\Storage::disk('public')->copy($oldPath, $newPath);
                            $el['src'] = '/storage/' . $newPath;
                        }
                    }
                }
            }
        }
        $template = \App\Models\WelcomeTemplate::create([
            'name' => $payload['name'],
            'config' => json_encode($configData)
        ]);

        return response()->json(['status' => 'success', 'message' => 'Lưu mẫu thành công!']);
    }
    
    public function deleteTemplate($id)
    {
        $template = \App\Models\WelcomeTemplate::find($id);
        
        if (!$template) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy mẫu!'], 404);
        }

        $config = json_decode($template->config, true);
        
        if (!empty($config['bg_image']) && str_starts_with($config['bg_image'], '/storage/templates/')) {
            $path = str_replace('/storage/', '', $config['bg_image']);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }

        if (!empty($config['elements'])) {
            foreach ($config['elements'] as $el) {
                if (isset($el['type']) && $el['type'] === 'image' && isset($el['src']) && str_starts_with($el['src'], '/storage/templates/')) {
                    $path = str_replace('/storage/', '', $el['src']);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                }
            }
        }
        $template->delete();

        return response()->json(['status' => 'success', 'message' => 'Xóa mẫu thành công!']);
    }

    //Chớp mắt
    public function toggleLiveness($id)
    {
        // Tìm cuộc họp dựa vào ID
        $meeting = Meeting::findOrFail($id);
        
        $meeting->require_blink = !$meeting->require_blink;
        $meeting->save();

        return back()->with('success', 'Đã cập nhật cấu hình chống giả mạo (Yêu cầu chớp mắt)!');
    }

    /**
     * API Cung cấp dữ liệu thống kê Real-time cho Dashboard
     */
    public function realtimeStats(Meeting $meeting)
    {
        $total = $meeting->guests()->count();
        $checkedIn = $meeting->guests()->where('is_attended', true)->count();
        $percentage = $total > 0 ? round(($checkedIn / $total) * 100, 1) : 0;

        $checkins = $meeting->guests()
            ->where('is_attended', true)
            ->whereNotNull('updated_at')
            ->orderBy('updated_at')
            ->get();

        $groupedData = [];
        
        foreach ($checkins as $guest) {
            // Lấy thời gian update và làm tròn phút xuống bội số của 10
            $time = \Carbon\Carbon::parse($guest->updated_at)->timezone('Asia/Ho_Chi_Minh');
            $minute = $time->format('i');
            $roundedMinute = floor($minute / 10) * 10;
            
            $timeLabel = $time->format('H:') . str_pad($roundedMinute, 2, '0', STR_PAD_LEFT);
            
            if (!isset($groupedData[$timeLabel])) {
                $groupedData[$timeLabel] = 0;
            }
            $groupedData[$timeLabel]++;
        }

        $chartLabels = array_keys($groupedData);
        $chartData = array_values($groupedData);

        if (empty($chartLabels)) {
            $chartLabels = [\Carbon\Carbon::now()->timezone('Asia/Ho_Chi_Minh')->format('H:i')];
            $chartData = [0];
        }

        return response()->json([
            'total' => $total,
            'checked_in' => $checkedIn,
            'percentage' => $percentage,
            'chart_labels' => $chartLabels,
            'chart_data' => $chartData
        ]);
    }

    /**
     * Xuất file Excel Danh sách đại biểu
     */
    public function exportGuests(Meeting $meeting)
    {
       /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasPermissionTo('attendance.export')) {
            abort(403, 'Bạn không có quyền này!');
        }
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Danh sách Đại biểu');

        // Tiêu đề chính
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'DANH SÁCH ĐẠI BIỂU');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => 'FF1E1B4B']], 
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(35);

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Sự kiện: ' . $meeting->title);
        
        $sheet->mergeCells('A3:F3');
        $sheet->setCellValue('A3', 'Thời gian: ' . \Carbon\Carbon::parse($meeting->start_time)->format('H:i d/m/Y') . '  đến  ' . \Carbon\Carbon::parse($meeting->end_time)->format('H:i d/m/Y'));
        
        $sheet->mergeCells('A4:F4');
        $sheet->setCellValue('A4', 'Địa điểm: ' . $meeting->location);

        $sheet->getStyle('A2:A4')->applyFromArray([
            'font' => ['size' => 11, 'italic' => true, 'color' => ['argb' => 'FF475569']], 
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(20);

        $headerRow = 6;
        $headers = [
            'A' => 'STT', 
            'B' => 'Họ và tên', 
            'C' => 'Email', 
            'D' => 'Chức vụ', 
            'E' => 'Vị trí ghế', 
            'F' => 'Trạng thái'
        ];
        
        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . $headerRow, $text);
        }

        $sheet->getStyle('A6:F6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F46E5'], 
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF312E81']],
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(30);

        // ==========================================
        // 3. ĐỔ DỮ LIỆU VÀ TÔ MÀU TRẠNG THÁI
        // ==========================================
        $row = 7;
        foreach ($meeting->guests as $index => $guest) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $guest->full_name);
            $sheet->setCellValue('C' . $row, $guest->email);
            $sheet->setCellValue('D' . $row, $guest->position);
            $sheet->setCellValue('E' . $row, $guest->seat_location);
            
            $status = $guest->is_attended ? 'Đã Check-in' : 'Vắng mặt';
            $sheet->setCellValue('F' . $row, $status);

            if ($guest->is_attended) {
                $sheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FF10B981');
                $sheet->getStyle('F' . $row)->getFont()->setBold(true);
            } else {
                $sheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FFEF4444'); 
            }

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}:F{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            $sheet->getStyle("A{$row}:F{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            $sheet->getRowDimension($row)->setRowHeight(24);

            $row++;
        }

        if ($row > 7) {
            $sheet->getStyle('A7:F' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']], // Màu viền Slate 300
                ],
            ]);
        }

        $sheet->getColumnDimension('A')->setWidth(8);  
        $sheet->getColumnDimension('B')->setAutoSize(true); 
        $sheet->getColumnDimension('C')->setAutoSize(true); 
        $sheet->getColumnDimension('D')->setAutoSize(true); 
        $sheet->getColumnDimension('E')->setWidth(18); 
        $sheet->getColumnDimension('F')->setWidth(20); 

        $row += 2;
        $sheet->mergeCells("D{$row}:F{$row}");
        $sheet->setCellValue("D{$row}", '.........., Ngày ...... tháng ...... năm 20...');
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D{$row}")->getFont()->setItalic(true);

        $row++;
        $sheet->mergeCells("D{$row}:F{$row}");
        $sheet->setCellValue("D{$row}", 'NGƯỜI XUẤT BÁO CÁO');
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D{$row}")->getFont()->setBold(true);

        $row += 4; // Bỏ cách vài dòng để ký tên
        $sheet->mergeCells("D{$row}:F{$row}");
        $sheet->setCellValue("D{$row}", '(Ký và ghi rõ họ tên)');
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D{$row}")->getFont()->setItalic(true);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Bao_Cao_Dai_Bieu_Su_Kien_' . $meeting->id . '.xlsx';

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$fileName.'"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    //Phân quyền
    private function authorizeMeetingAction($meeting, $permissionName)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('Admin') || $meeting->user_id === $user->id) {
            return true;
        }

        if (!$user->hasPermissionTo($permissionName)) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này trên hệ thống!');
        }
    }

    // ==========================================
    // THÙNG RÁC 
    // ==========================================
    public function trashed()
    {
        /** @var \App\Models\User $user */ 
        $user = Auth::user();

        if (!$user || !$user->can('meeting.delete')) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này!');
        }
        $meetings = Meeting::onlyTrashed()->with('user')->orderBy('deleted_at', 'desc')->paginate(10);
        return view('meetings.trashed', compact('meetings'));
    }

    // 2. Tại hàm Khôi phục sự kiện
    public function restore($id)
    {

        /** @var \App\Models\User $user */ 
        $user = Auth::user();

        if (!$user || !$user->can('meeting.delete')) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này!');
        }

        $meeting = Meeting::onlyTrashed()->findOrFail($id);
        $meeting->restore();

        return redirect()->route('meetings.trashed')->with('success', 'Đã khôi phục sự kiện: ' . $meeting->title);
    }

    // 3. Tại hàm xóa vĩnh viễn 
    public function forceDelete($id)
    {
        /** @var \App\Models\User $user */ 
        $user = Auth::user();
        if (!$user || !$user->can('meeting.force_delete')) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này!');
        }

        $meeting = Meeting::onlyTrashed()->findOrFail($id);
        
        \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory('meetings/' . $id);
        $meeting->forceDelete();

        return redirect()->route('meetings.trashed')->with('success', 'Đã xóa vĩnh viễn sự kiện ra khỏi hệ thống.');
    }

    // ==========================================
    // XÁC THỰC KHUÔN MẶT 
    // ==========================================
    public function validateFacesView($id)
    {
        $meeting = Meeting::findOrFail($id);
        
        $guests = Guest::where('meeting_id', $id)
                    //    ->whereNull('face_vector')
                       ->get();

        return view('meetings.validate_faces', compact('meeting', 'guests'));
    }

    public function processValidation(Request $request, $id)
    {
        $guestId = $request->input('guest_id');
        $guest = Guest::where('meeting_id', $id)->findOrFail($guestId);
        
        if (empty($guest->image_filename)) {
            return response()->json(['status' => 'error', 'message' => 'Đại biểu chưa cập nhật ảnh.']);
        }

        $imagePath = storage_path('app/public/meetings/' . $id . '/faces/' . $guest->image_filename);

        if (!file_exists($imagePath) || is_dir($imagePath)) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy file ảnh gốc trên máy chủ.']);
        }

        try {
            $response = Http::timeout(15)->attach(
                'file', file_get_contents($imagePath), $guest->image_filename
            )->post('http://127.0.0.1:8001/xac_thuc_anh');

            if ($response->successful()) {
                $data = $response->json(); 
                
                if (isset($data['status']) && $data['status'] === 'success') {
                    $binaryVector = pack('f*', ...$data['vector']);
                    $guest->face_vector = $binaryVector;
                    
                    $guest->save();
                    
                    return response()->json(['status' => 'success']);
                } else {
                    return response()->json(['status' => 'error', 'message' => $data['message'] ?? 'Lỗi không xác định từ AI']);
                }
            }
            return response()->json(['status' => 'error', 'message' => 'AI Server phản hồi mã lỗi: ' . $response->status()], 500);
            
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Không kết nối được AI (Port 8001 đang tắt).'], 500);
        }
    }
}