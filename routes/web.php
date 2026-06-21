<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\AdminUserController;

// ==========================================
// 1. KHU VỰC CHƯA ĐĂNG NHẬP (GUEST)
// ==========================================

// Trỏ trang chủ và trang login về chung 1 giao diện
Route::get('/', [AuthController::class, 'showAuthForm']);
Route::get('/login', [AuthController::class, 'showAuthForm'])->name('login');

// Xử lý Form đăng nhập / đăng ký / đăng xuất
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Đăng nhập qua MXH
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::get('/auth/facebook', [GoogleController::class, 'redirectToFacebook'])->name('facebook.login');
Route::get('/auth/facebook/callback', [GoogleController::class, 'handleFacebookCallback']);


// ==========================================
// 2. KHU VỰC ĐÃ ĐĂNG NHẬP (AUTH)
// ==========================================
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Quản lý thông tin cá nhân
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Quản lý Cuộc họp (CRUD)
    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    // Route::get('/meetings/create', function () { return view('meetings.create'); })->name('meetings.create');
    // Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::get('/meetings/create', function () { return view('meetings.create'); })
        ->middleware('permission:meeting.create') // Gắn ổ khóa
        ->name('meetings.create');

    Route::post('/meetings', [MeetingController::class, 'store'])
        ->middleware('permission:meeting.create') // Gắn ổ khóa
        ->name('meetings.store');

    Route::get('/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
    Route::get('/meetings/{meeting}/edit', [MeetingController::class, 'edit'])->name('meetings.edit');
    Route::put('/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
    Route::delete('/meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');

    // Các tính năng tiện ích của Cuộc họp
    Route::get('/meetings/{meeting}/welcome', [MeetingController::class, 'welcomeScreen'])->name('meetings.welcome');
    Route::get('/meetings/{meeting}/start-camera', [MeetingController::class, 'startCamera'])->name('meetings.start_camera'); //Không sài nữa
    Route::get('/meetings/{meeting}/online', [MeetingController::class, 'onlineCheckin'])->name('meetings.online');
    Route::post('/meetings/{meeting}/send-tickets', [MeetingController::class, 'sendTickets'])->name('meetings.send_tickets');
    Route::get('/meetings/{meeting}/scan-qr', [MeetingController::class, 'scanQr'])->name('meetings.scan_qr');
    Route::post('/meetings/{meeting}/add-guest', [MeetingController::class, 'addGuest'])->name('meetings.add_guest');
    Route::post('/meetings/{meeting}/welcome-config', [MeetingController::class, 'updateWelcomeConfig'])->name('meetings.update_welcome_config');
    Route::get('/meetings/{meeting}/designer', [App\Http\Controllers\MeetingController::class, 'designer'])->name('meetings.designer');
    Route::post('/api/meetings/{meeting}/save-design', [App\Http\Controllers\MeetingController::class, 'saveDesign'])->name('api.save_design');
    Route::get('/meetings/{meeting}/game', [MeetingController::class, 'game'])->name('meetings.game');

    // Quản lý Đại biểu
    Route::post('/guests/{guest}/update-face', [GuestController::class, 'updateFace']);

    // Các API nội bộ
    Route::get('/start-ai-api', [MeetingController::class, 'startApiServer'])->name('api.start_server');
    Route::get('/api/meetings/{meeting}/latest-checkin', [MeetingController::class, 'latestCheckin'])->name('api.latest_checkin');
    Route::post('/api/meetings/process-qr', [MeetingController::class, 'processQrScan'])->name('api.process_qr');
    Route::get('/api/global-search', [MeetingController::class, 'globalSearch']);
    // API lấy danh sách cổng đang hoạt động và API gửi nhịp tim
    Route::get('/api/meetings/{meeting}/active-gates', [MeetingController::class, 'getActiveGates']);
    Route::post('/api/meetings/{meeting}/gate-heartbeat', [MeetingController::class, 'gateHeartbeat']);

    // Admin: Quản lý User (CRUD)
    Route::prefix('admin')->name('admin.')->group(function () {
        // 1. Quản lý user & cấp quyền riêng lẻ
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');

        // 2. Quản lý Ma trận chức vụ (BẠN ĐANG THIẾU 2 DÒNG NÀY TRONG WEB.PHP)
        Route::get('/matrix', [AdminUserController::class, 'matrix'])->name('matrix.index');
        Route::post('/matrix/update', [AdminUserController::class, 'updateMatrix'])->name('matrix.update');
    });
});