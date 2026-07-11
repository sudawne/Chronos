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
use App\Http\Controllers\NotificationController;

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
// ==========================================
// 2. KHU VỰC ĐÃ ĐĂNG NHẬP (AUTH TIÊU CHUẨN)
// ==========================================
Route::middleware(['auth'])->group(function () {
    
    // Tiện ích chung ai cũng vào được
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/api/global-search', [MeetingController::class, 'globalSearch']);
    
    // Lấy danh sách cuộc họp
    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');

    // ------------------------------------------
    // [QUYỀN TẠO CUỘC HỌP] - PHẢI ĐẶT TRÊN MEETING.SHOW
    // ------------------------------------------
    Route::middleware(['permission:meeting.create'])->group(function () {
        Route::get('/meetings/create', function () { return view('meetings.create'); })->name('meetings.create');
        Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    });

    // ------------------------------------------
    // CHI TIẾT CUỘC HỌP (ROUTE ĐỘNG {meeting})
    // ------------------------------------------
    // Dòng này phải đặt SAU /meetings/create để tránh bị nuốt route
    Route::get('/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');

    // ------------------------------------------
    // [QUYỀN SỬA CUỘC HỌP]
    // ------------------------------------------
    Route::middleware(['permission:meeting.edit'])->group(function () {
        Route::get('/meetings/{meeting}/edit', [MeetingController::class, 'edit'])->name('meetings.edit');
        Route::put('/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
        Route::post('/meetings/{meeting}/welcome-config', [MeetingController::class, 'updateWelcomeConfig'])->name('meetings.update_welcome_config');
        Route::post('/meetings/{meeting}/toggle-liveness', [MeetingController::class, 'toggleLiveness'])->name('meetings.toggle_liveness');
        Route::put('/guests/{guest}', [GuestController::class, 'update'])->name('guests.update');
        Route::post('/guests/{guest}/update-face', [GuestController::class, 'updateFace'])->name('guests.update_face');
    });

    // ------------------------------------------
    // [QUYỀN XÓA CUỘC HỌP & THÙNG RÁC]
    // ------------------------------------------
    Route::middleware(['permission:meeting.delete'])->group(function () {
        Route::delete('/meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');
        Route::delete('/guests/{guest}', [GuestController::class, 'destroy'])->name('guests.destroy');
        Route::get('/meetings-trashed', [MeetingController::class, 'trashed'])->name('meetings.trashed');
        Route::patch('/meetings/{id}/restore', [MeetingController::class, 'restore'])->name('meetings.restore');
        Route::delete('/meetings/{id}/force-delete', [MeetingController::class, 'forceDelete'])->name('meetings.force_delete');
    });

    // ------------------------------------------
    // [QUYỀN THIẾT KẾ MÀN HÌNH CHÀO MỪNG]
    // ------------------------------------------
    Route::middleware(['permission:meeting.design'])->group(function () {
        Route::get('/meetings/{meeting}/designer', [MeetingController::class, 'designer'])->name('meetings.designer');
        Route::post('/api/meetings/{meeting}/save-design', [MeetingController::class, 'saveDesign'])->name('api.save_design');
        Route::get('/api/welcome-templates', [MeetingController::class, 'getTemplates'])->name('api.get_templates');
        Route::post('/api/welcome-templates', [MeetingController::class, 'saveTemplate'])->name('api.save_template');
        Route::delete('/api/welcome-templates/{id}', [MeetingController::class, 'deleteTemplate'])->name('api.delete_template');
    });

    // ------------------------------------------
    // [QUYỀN VẬN HÀNH AI & ĐIỂM DANH]
    // ------------------------------------------
    Route::middleware(['permission:attendance.manage'])->group(function () {
        Route::post('/meetings/{meeting}/send-tickets', [MeetingController::class, 'sendTickets'])->name('meetings.send_tickets');
        Route::post('/meetings/{meeting}/add-guest', [MeetingController::class, 'addGuest'])->name('meetings.add_guest');
        Route::post('/meetings/{meeting}/send-photo-requests', [MeetingController::class, 'sendPhotoRequests'])->name('meetings.send-photo-requests');
        
        // Quản lý API Server AI
        Route::get('/start-ai-api', [MeetingController::class, 'startApiServer'])->name('api.start_server');
        Route::get('/meetings/{id}/validate-faces', [MeetingController::class, 'validateFacesView'])->name('meetings.validate_faces');
        Route::post('/meetings/{id}/process-validation', [MeetingController::class, 'processValidation']);
    });

    // Các Route phục vụ màn hình chạy trực tiếp (Không cần khóa quyền nghiêm ngặt để nhân viên trực có thể mở)
    Route::get('/meetings/{meeting}/welcome', [MeetingController::class, 'welcomeScreen'])->name('meetings.welcome');
    Route::get('/meetings/{meeting}/online', [MeetingController::class, 'onlineCheckin'])->name('meetings.online');
    Route::get('/meetings/{meeting}/game', [MeetingController::class, 'game'])->name('meetings.game');
    Route::get('/meetings/{meeting}/scan-qr', [MeetingController::class, 'scanQr'])->name('meetings.scan_qr');
    Route::post('/api/meetings/process-qr', [MeetingController::class, 'processQrScan'])->name('api.process_qr');
    Route::get('/api/meetings/{meeting}/latest-checkin', [MeetingController::class, 'latestCheckin'])->name('api.latest_checkin');
    Route::get('/api/meetings/{meeting}/realtime-stats', [MeetingController::class, 'realtimeStats']);
    Route::get('/api/meetings/{meeting}/active-gates', [MeetingController::class, 'getActiveGates']);
    Route::post('/api/meetings/{meeting}/gate-heartbeat', [MeetingController::class, 'gateHeartbeat']);
    Route::get('/meetings/{meeting}/export-guests', [MeetingController::class, 'exportGuests'])->name('meetings.export_guests');

    // Route khách tự up ảnh (Signed url)
    Route::get('/meetings/{meeting}/guests/{guest}/photo', [MeetingController::class, 'guestPhotoForm'])->name('guest.photo.form')->middleware('signed');
    Route::post('/meetings/{meeting}/guests/{guest}/photo', [MeetingController::class, 'guestPhotoUpload'])->name('guest.photo.upload')->middleware('signed');

    // ==========================================
    // 3. KHU VỰC QUẢN TRỊ VIÊN HỆ THỐNG (ADMIN)
    // ==========================================
    Route::middleware(['permission:admin.users'])->group(function () { 
        Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::put('/admin/users/{user}/change-password', [AdminUserController::class, 'changePassword'])->name('admin.users.change-password');
        
        Route::get('/admin/permissions/matrix', [AdminUserController::class, 'matrix'])->name('admin.matrix.index');
        Route::post('/admin/permissions/matrix/update', [AdminUserController::class, 'matrixUpdate'])->name('admin.permissions.matrix.update');
    });

    Route::get('/api/notifications/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread');
    Route::post('/api/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});