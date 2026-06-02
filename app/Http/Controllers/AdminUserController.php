<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    // HÀM KIỂM TRA CHỨC VỤ BẰNG SPATIE (Thay cho Auth::id() === 1)
    private function checkAdmin() 
    {
        // 1. Kiểm tra xem người dùng đã đăng nhập chưa
        if (!Auth::check()) {
            abort(403, 'Từ chối truy cập! Vui lòng đăng nhập.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Bây giờ VS Code sẽ nhận diện được biến $user chính là model User của bạn
        // Và hàm hasRole() sẽ trỏ thành công, không bao giờ bị lỗi nữa!
        if (!$user->hasRole('Admin')) {
            abort(403, 'Từ chối truy cập! Chỉ Admin mới được thực hiện thao tác này.');
        }
    }

    // ==========================================
    // PHẦN 1: QUẢN LÝ TỪNG USER (QUYỀN RIÊNG LẺ)
    // ==========================================
    public function index()
    {
        $this->checkAdmin();
        $users = User::with('roles', 'permissions')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        $this->checkAdmin();
        $roles = Role::all();
        $permissions = Permission::all();
        return view('admin.users.edit', compact('user', 'roles', 'permissions'));
    }

    public function update(Request $request, User $user)
    {
        $this->checkAdmin();
        
        $user->syncRoles($request->input('roles', []));
        $user->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật phân quyền cho ' . $user->name . ' thành công!');
    }

    // ==========================================
    // PHẦN 2: QUẢN LÝ MA TRẬN NHÓM (ROLES)
    // ==========================================
    public function matrix()
    {
        $this->checkAdmin();
        $roles = Role::all();
        
        $modules = [
            'Tài khoản Admin' => ['Xem' => 'user.view', 'Thêm mới' => 'user.create', 'Chỉnh sửa' => 'user.edit', 'Xóa' => 'user.delete'],
            'Quản lý Cuộc họp' => ['Xem' => 'meeting.view', 'Thêm mới' => 'meeting.create', 'Chỉnh sửa' => 'meeting.edit', 'Xóa' => 'meeting.delete'],
            'Điểm danh & AI' => ['Xem danh sách' => 'attendance.view', 'Thao tác quét QR/AI' => 'attendance.manage']
        ];

        return view('admin.permissions.matrix', compact('roles', 'modules'));
    }

    public function updateMatrix(Request $request)
    {
        $this->checkAdmin();
        $rolesData = $request->input('roles', []);
        
        foreach (Role::all() as $role) {
            $role->syncPermissions($rolesData[$role->name] ?? []);
        }

        return redirect()->back()->with('success', 'Đã cập nhật Ma trận Phân quyền Nhóm thành công!');
    }
}