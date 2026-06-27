<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    // HÀM KIỂM TRA QUYỀN TRUY CẬP TRANG QUẢN TRỊ
    private function checkAdmin() 
    {
        if (!Auth::check()) {
            abort(403, 'Từ chối truy cập! Vui lòng đăng nhập.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('Admin')) {
            abort(403, 'Từ chối truy cập! Chỉ Admin mới được thực hiện thao tác này.');
        }
    }

    // ==========================================
    // PHẦN 1: QUẢN LÝ TỪNG USER (users/index & edit)
    // ==========================================
    public function index()
    {
        $this->checkAdmin();
        $users = User::with('roles', 'permissions')->paginate(15);
        
        // Trỏ đúng vào thư mục: resources/views/admin/users/index.blade.php
        return view('admin.users.index', compact('users')); 
    }

    public function edit(User $user)
    {
        $this->checkAdmin();
        $roles = Role::all();
        $permissions = Permission::all();
        
        // Trỏ đúng vào thư mục: resources/views/admin/users/edit.blade.php
        return view('admin.users.edit', compact('user', 'roles', 'permissions'));
    }

    public function update(Request $request, User $user)
    {
        $this->checkAdmin();
        
        // Cập nhật Nhóm chức vụ (Quyền to) & Tính năng lẻ (Quyền nhỏ)
        $user->syncRoles($request->input('roles', []));
        $user->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.users.index')->with('success', 'Đã cập nhật phân quyền cho ' . $user->name . ' thành công!');
    }

    // ==========================================
    // PHẦN 2: QUẢN LÝ MA TRẬN NHÓM (permissions/matrix)
    // ==========================================
    public function matrix()
    {
        $this->checkAdmin();
        $roles = Role::all();
        
        // Định nghĩa các nhóm quyền hiển thị trên bảng Ma trận
        $modules = [
            'Quản trị Hệ thống' => ['Xem tài khoản' => 'user.view', 'Sửa tài khoản' => 'user.edit', 'Xóa tài khoản' => 'user.delete'],
            'Quản lý Sự kiện' => ['Xem kho lưu trữ' => 'meeting.view', 'Tạo sự kiện' => 'meeting.create', 'Thiết kế Welcome' => 'meeting.design', 'Xóa sự kiện' => 'meeting.delete'],
            'Giám sát Điểm danh' => ['Xem thống kê Dashboard' => 'attendance.view', 'Bật Camera & AI' => 'attendance.manage', 'Xuất báo cáo Excel' => 'attendance.export']
        ];

        // Trỏ đúng vào thư mục: resources/views/admin/permissions/matrix.blade.php
        return view('admin.permissions.matrix', compact('roles', 'modules'));
    }

    public function matrixUpdate(Request $request)
    {
        $this->checkAdmin();
        
        $roles = Role::all();
        $rolesData = $request->input('roles', []);

        foreach ($roles as $role) {
            // Lấy danh sách checkbox, nếu không tick thì trả về mảng rỗng
            $permissionsToSync = $rolesData[$role->name] ?? [];
            $role->syncPermissions($permissionsToSync);
        }

        return redirect()->back()->with('success', 'Lưu cấu hình Ma trận Phân quyền thành công!');
    }
}