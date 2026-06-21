<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\Guest;
use Illuminate\Support\Facades\Storage;
// Thêm thư viện đọc Excel vào đây
use PhpOffice\PhpSpreadsheet\IOFactory; 
use Illuminate\Support\Facades\Http;
use App\Mail\GuestTicketMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class MeetingController extends Controller
{
    public function store(Request $request)
    {
        // 1. Kiểm tra tính hợp lệ
        $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'required|string|max:255',
            'file_excel' => 'required|file|mimes:xlsx,xls,csv,txt',
            'file_anh' => 'required',
        ]);

        // 2. Lưu thông tin cuộc họp
        $meeting = Meeting::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'description' => $request->description, 
            'recognition_threshold' => $request->recognition_threshold ?? 0.55,
        ]);

        // 3. ĐỌC FILE EXCEL & TẠO GUEST
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
        
        // 4. BẮN ẢNH SANG PYTHON API LẤY VECTOR (MICROSERVICES)
        $successCount = 0;
        $errorCount = 0;

        if ($request->hasFile('file_anh')) {
            // Lưu ảnh vào disk để backup
            $folderPath = "meetings/{$meeting->id}/faces";

            foreach ($request->file('file_anh') as $image) {
                $filename = $image->getClientOriginalName();
                $image->storeAs($folderPath, $filename, 'public');

                // Tìm khách mời có tên file ảnh tương ứng
                $guest = Guest::where('meeting_id', $meeting->id)->where('image_filename', $filename)->first();

                if ($guest) {
                    try {
                        // Gọi API Python
                        $response = Http::timeout(10)->attach(
                            'file', file_get_contents($image->getRealPath()), $filename
                        )->post('http://localhost:8001/register_face');

                        if ($response->successful() && $response['status'] === 'success') {
                            $vectorArray = $response['vector'];
                            // Chuyển mảng số thành nhị phân BLOB
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

        // 5. Kết luận
        $msg = "Khởi tạo thành công! Đã nạp AI cho $successCount khách. Lỗi: $errorCount.";
        return redirect()->route('dashboard')->with('success', $msg);
    }

    // Thêm hàm index() để lấy toàn bộ cuộc họp kèm số lượng khách mời
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            // Admin thì lấy TẤT CẢ cuộc họp, kèm theo thông tin người tạo
            $meetings = Meeting::with('user')->orderBy('created_at', 'desc')->paginate(10);
        } else {
            // Quản lý thì CHỈ LẤY CỦA MÌNH
            $meetings = Meeting::where('user_id', Auth::id())->orderBy('created_at', 'desc')->paginate(10);
        }
        return view('meetings.index', compact('meetings'));
    }

    // Xem chi tiết cuộc họp và danh sách thành viên (Guests)
    public function show(Meeting $meeting)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('Admin') && $meeting->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền quản lý cuộc họp này!');
        }
        $meeting->load('guests'); // Eager load danh sách khách mời
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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('Admin') && $meeting->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền quản lý cuộc họp này!');
        }
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

        return redirect()->route('meetings.index')->with('success', 'Cập nhật thông tin cuộc họp thành công!');
    }

    // Xóa cuộc họp (Hệ thống tự xóa guests theo ràng buộc cascade đã cài ở Migration)
    public function destroy(Meeting $meeting)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('Admin') && $meeting->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền quản lý cuộc họp này!');
        }
        $meeting->delete();
        return redirect()->route('meetings.index')->with('success', 'Đã xóa cuộc họp thành công!');
    }

    // Hàm mở trang Màn hình Chào mừng (Welcome Screen)
    public function welcomeScreen(Meeting $meeting)
    {
        return view('meetings.welcome', compact('meeting'));
    }

    public function latestCheckin(Meeting $meeting)
    {
        // 1. Lấy người vừa điểm danh gần nhất
        $latestGuest = Guest::where('meeting_id', $meeting->id)
            ->where('is_attended', true)
            ->orderBy('updated_at', 'desc')
            ->first();

        // 2. KHẮC PHỤC MÚI GIỜ: Dùng Carbon của PHP để đếm giây (Chính xác 100%)
        // Chỉ lấy nếu người này vừa điểm danh trong vòng 6 giây qua
        if ($latestGuest && $latestGuest->updated_at->diffInSeconds(now()) <= 6) {
            
            // Tìm ảnh Live Snapshot
            $fileName = "live_face_{$latestGuest->id}.jpg";
            $liveImagePath = "meetings/{$meeting->id}/live_faces/{$fileName}";

            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($liveImagePath)) {
                // Thêm timestamp để trình duyệt luôn tải ảnh mới nhất, không bị kẹt cache
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
    public function startCamera(Meeting $meeting)
    {
        // 1. Thư mục chứa code AI và file mô hình (best.pt)
        $aiFolder = 'D:\KLTN\AI'; 
        
        // 2. Tên file chạy
        $pythonScript = 'diem_danh_live.py'; 

        // 3. Đường dẫn tuyệt đối tới Python của bạn
        $pythonExecutable = 'C:\Users\Zbook\AppData\Local\Programs\Python\Python311\python.exe'; 

        // 4. Lệnh chạy siêu việt:
        // - cd /d : Bắt buộc di chuyển vào đúng thư mục AI trước khi chạy
        // - start cmd /k : Bật 1 cửa sổ CMD độc lập và GIỮ NÓ MỞ (lệnh /k) dù có lỗi xảy ra
        $command = 'cd /d "' . $aiFolder . '" && start cmd /k ""' . $pythonExecutable . '" "' . $pythonScript . '" ' . $meeting->id . '"';

        // 5. Chạy ngầm không chờ
        pclose(popen($command, "r"));

        return redirect()->back()->with('success', 'Đang nạp model AI và khởi động Camera... Vui lòng đợi cửa sổ Terminal bật lên!');
    }
    public function onlineCheckin(Meeting $meeting)
    {
        return view('meetings.online', compact('meeting'));
    }
    public function startApiServer()
    {
        // 1. Thư mục chứa file api_ai.py của bạn
        $aiFolder = 'D:\KLTN\AI'; 

        // 2. Đường dẫn tuyệt đối tới file python.exe
        $pythonExecutable = 'C:\Users\Zbook\AppData\Local\Programs\Python\Python311\python.exe'; 

        // 3. Lệnh chạy Uvicorn thông qua Python module (-m)
        // Dùng start cmd /k để bật lên 1 cửa sổ đen theo dõi luồng API
        $command = 'cd /d "' . $aiFolder . '" && start cmd /k ""' . $pythonExecutable . '" -m uvicorn api_ai:app --host 0.0.0.0 --port 8001"';

        // 4. Chạy lệnh ngầm và không làm treo Web
        pclose(popen($command, "r"));

        return redirect()->back()->with('success', 'Đã khởi động Máy chủ AI API thành công! Bạn có thể bắt đầu điểm danh.');
    }

    // Gửi mail vé mời cho khách tham dự (chứa QR Code)
    public function sendTickets(Meeting $meeting)
    {
        // Chỉ lấy những khách mời CÓ ĐỊA CHỈ EMAIL
        $guests = $meeting->guests()->whereNotNull('email')->get();
        $count = 0;

        foreach ($guests as $guest) {
            try {
                // Gửi email cho khách mời
                Mail::to($guest->email)->send(new GuestTicketMail($guest, $meeting));
                $count++;
            } catch (Exception $e) {
                // Ghi log lỗi bằng Facade đã được import
                Log::error("Lỗi gửi mail cho " . $guest->email . ": " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Đã gửi thành công $count vé mời QR Code qua Email!");
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
        // 1. Validate form nhập
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'position' => 'nullable|string|max:255',
            'seat_location' => 'nullable|string|max:255',
            'file_anh' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // 2. Tạo Guest mới
        $guest = Guest::create([
            'meeting_id' => $meeting->id,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'position' => $request->position,
            'seat_location' => $request->seat_location,
            'is_attended' => false,
        ]);

        // 3. Nếu người dùng có tải lên file ảnh, gọi API Python để lấy Vector
        if ($request->hasFile('file_anh')) {
            $image = $request->file('file_anh');
            $filename = $image->getClientOriginalName();
            
            // Cập nhật tên file vào CSDL
            $guest->update(['image_filename' => $filename]);

            try {
                $response = \Illuminate\Support\Facades\Http::timeout(15)->attach(
                    'file', file_get_contents($image->getRealPath()), $filename
                )->post('http://localhost:8001/register_face');

                if ($response->successful() && $response['status'] === 'success') {
                    $binaryVector = pack('f*', ...$response['vector']);
                    $guest->update(['face_vector' => $binaryVector]);
                    return redirect()->back()->with('success', 'Đã thêm đại biểu ' . $guest->full_name . ' và nạp khuôn mặt thành công!');
                } else {
                    return redirect()->back()->with('warning', 'Đã thêm đại biểu ' . $guest->full_name . ' NHƯNG lỗi AI: ' . ($response['message'] ?? 'Không trích xuất được khuôn mặt.'));
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('warning', 'Đã thêm đại biểu ' . $guest->full_name . ' NHƯNG lỗi kết nối Server AI (Port 8001 đang tắt).');
            }
        }

        // 4. Nếu không tải ảnh lên, chỉ báo thêm thành công
        return redirect()->back()->with('success', 'Đã thêm đại biểu ' . $guest->full_name . ' thành công! Vui lòng nạp ảnh nhận diện sau.');
    }

    public function globalSearch(Request $request)
    {
        $query = $request->query('query');
        
        if (empty($query)) {
            return response()->json([]);
        }

        // Tìm kiếm các cuộc họp có tiêu đề hoặc địa điểm khớp với từ khóa nhập vào
        $meetings = \App\Models\Meeting::where('title', 'LIKE', '%' . $query . '%')
            ->orWhere('location', 'LIKE', '%' . $query . '%')
            ->select('id', 'title', 'location')
            ->take(8) // Giới hạn tối đa 8 kết quả trả về để tối ưu tốc độ render
            ->get();

        return response()->json($meetings);
    }
    public function updateWelcomeConfig(Request $request, Meeting $meeting)
    {
        // Lấy cấu hình cũ nếu có
        $config = $meeting->welcome_config ? json_decode($meeting->welcome_config, true) : [];

        // Xử lý upload ảnh nền
        if ($request->hasFile('bg_image')) {
            $path = $request->file('bg_image')->store("meetings/{$meeting->id}/welcome", 'public');
            $config['bg_image'] = '/storage/' . $path;
        }
        
        // Xử lý upload Logo
        if ($request->hasFile('logo_image')) {
            $path = $request->file('logo_image')->store("meetings/{$meeting->id}/welcome", 'public');
            $config['logo_image'] = '/storage/' . $path;
        }

        // Lấy các thông số CSS text
        $config['name_color'] = $request->input('name_color', '#ffffff');
        $config['name_size'] = $request->input('name_size', '3rem');
        $config['text_align'] = $request->input('text_align', 'center');
        $config['box_position_y'] = $request->input('box_position_y', 'center'); // top, center, bottom
        
        // Lưu lại vào DB dưới dạng JSON
        $meeting->update([
            'welcome_config' => json_encode($config)
        ]);

        return back()->with('success', 'Đã cập nhật giao diện màn hình chào mừng thành công!');
    }

    // Trả về giao diện Designer
    public function designer(Meeting $meeting)
    {
        // Trích xuất cấu hình cũ (nếu có)
        $config = $meeting->welcome_config ? json_decode($meeting->welcome_config, true) : null;
        return view('meetings.designer', compact('meeting', 'config'));
    }

    // Nhận dữ liệu tọa độ, màu sắc từ JS và lưu lại
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

        // 1. TÁCH BASE64 THÀNH FILE ẢNH NỀN (BACKGROUND)
        if (!empty($payload['bg_image']) && str_starts_with($payload['bg_image'], 'data:image')) {
            $image_parts = explode(";base64,", $payload['bg_image']);
            $image_base64 = base64_decode($image_parts[1]);
            
            $fileName = 'bg_' . time() . '.png';
            $path = "meetings/{$meeting->id}/welcome/{$fileName}";
            
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $image_base64);
            $config['bg_image'] = '/storage/' . $path; // Chỉ lưu đường dẫn ngắn vào DB
        } else {
            $config['bg_image'] = $payload['bg_image']; // Giữ nguyên link ảnh cũ nếu không sửa
        }

        // 2. TÁCH BASE64 THÀNH FILE ẢNH CHO CÁC LOGO/HÌNH ẢNH NHỎ
        foreach ($config['elements'] as &$el) {
            if (isset($el['type']) && $el['type'] === 'image' && isset($el['src'])) {
                
                // Nếu phát hiện là chuỗi Base64 mới upload lên
                if (str_starts_with($el['src'], 'data:image')) {
                    $image_parts = explode(";base64,", $el['src']);
                    $image_base64 = base64_decode($image_parts[1]);
                    
                    $fileName = 'element_' . uniqid() . '.png';
                    $path = "meetings/{$meeting->id}/welcome/{$fileName}";
                    
                    \Illuminate\Support\Facades\Storage::disk('public')->put($path, $image_base64);
                    
                    // Thay thế chuỗi khổng lồ bằng đường dẫn file
                    $el['src'] = '/storage/' . $path; 
                }
            }
        }

        // 3. LƯU VÀO DATABASE (Lúc này json_encode rất nhẹ và an toàn)
        $meeting->update([
            'welcome_config' => json_encode($config)
        ]);

        return response()->json(['status' => 'success', 'message' => 'Đã lưu thiết kế!']);
    }
    // Mở trang Mini Game AI cho cuộc họp
    public function game(Meeting $meeting)
    {
        return view('meetings.game', compact('meeting'));
    }
}