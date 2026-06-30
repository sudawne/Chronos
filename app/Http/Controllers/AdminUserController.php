<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    // [ĐÃ SỬA]: Chuyển từ checkAdmin cứng nhắc sang checkPermission linh hoạt
    private function checkPermission($permissionCode) 
    {
        if (!Auth::check()) abort(403, 'Từ chối truy cập! Vui lòng đăng nhập.');
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Nếu là Admin thì cho qua, nếu không phải Admin thì kiểm tra xem có quyền lẻ tương ứng không
        if (!$user->hasRole('Admin') && !$user->can($permissionCode)) {
            abort(403, "Từ chối truy cập! Bạn cần có quyền [{$permissionCode}] để thực hiện thao tác này.");
        }
    }

    private function getModules()
    {
        return [
            'Quản trị Hệ thống' => [
                'Xem tài khoản' => 'user.view', 
                'Thêm mới' => 'user.create', 
                'Chỉnh sửa' => 'user.edit', 
                'Xóa' => 'user.delete',
                // [ĐÃ THÊM]: Quyền Custom theo yêu cầu của bạn
                'Phân quyền nâng cao (Custom)' => 'user.custom' 
            ],
            'Quản lý Sự kiện' => ['Xem kho lưu trữ' => 'meeting.view', 'Tạo sự kiện' => 'meeting.create', 'Thiết kế Welcome' => 'meeting.design', 'Xóa sự kiện' => 'meeting.delete'],
            'Giám sát Điểm danh' => ['Xem thống kê Dashboard' => 'attendance.view', 'Bật Camera & AI' => 'attendance.manage', 'Xuất báo cáo Excel' => 'attendance.export']
        ];
    }

    private function ensurePermissionsExist($modules)
    {
        foreach ($modules as $group => $actions) {
            foreach ($actions as $label => $code) {
                Permission::firstOrCreate([
                    'name' => $code,
                    'guard_name' => 'web'
                ]);
            }
        }
    }

    // ==========================================
    // PHẦN 1: QUẢN LÝ TỪNG USER
    // ==========================================
    public function index()
    {
        // Yêu cầu quyền xem user
        $this->checkPermission('user.view'); 
        $users = User::with('roles', 'permissions')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        // Yêu cầu quyền sửa user
        $this->checkPermission('user.edit'); 
        $roles = Role::all();
        $modules = $this->getModules(); 
        
        $this->ensurePermissionsExist($modules);
        
        return view('admin.users.edit', compact('user', 'roles', 'modules'));
    }

    public function update(Request $request, User $user)
    {
        // Yêu cầu quyền sửa user
        $this->checkPermission('user.edit'); 
        
        // 1. Cập nhật nhóm Chức vụ
        $user->syncRoles($request->input('roles', []));
        
        // 2. Tẩy não bộ nhớ Cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $user->refresh(); 
        
        // 3. Phân tách quyền độc lập (Custom)
        $rolePermissions = $user->getPermissionsViaRoles()->pluck('name')->toArray();
        $inputPermissions = $request->input('permissions', []);
        $permissionsToSync = array_diff($inputPermissions, $rolePermissions);
        
        $user->syncPermissions($permissionsToSync);

        // ==============================================================
        // [LƯỚI BẢO VỆ]: Tránh việc tự "đá" chính mình ra khỏi hệ thống
        // ==============================================================
        if (Auth::id() === $user->id) {
            // Nếu bạn vừa tự bỏ role Admin và cũng quên tích quyền Xem/Sửa tài khoản
            if (!$user->hasRole('Admin') && !$user->hasPermissionTo('user.view')) {
                // Hệ thống sẽ tự động ép cấp quyền để bạn không bị văng ra trang 403
                $user->givePermissionTo(['user.view', 'user.edit', 'user.custom']);
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật phân quyền cho ' . $user->name . ' thành công!');
    }

    // ==========================================
    // PHẦN 2: QUẢN LÝ MA TRẬN NHÓM
    // ==========================================
    public function matrix()
    {
        // Sử dụng quyền custom vừa tạo để bảo vệ khu vực Ma trận
        $this->checkPermission('user.custom'); 
        $roles = Role::all();
        $modules = $this->getModules();

        $this->ensurePermissionsExist($modules);

        return view('admin.permissions.matrix', compact('roles', 'modules'));
    }

    public function matrixUpdate(Request $request)
    {
        $this->checkPermission('user.custom');
        $rolesData = $request->input('roles', []);
        
        foreach (Role::all() as $role) {
            $role->syncPermissions($rolesData[$role->name] ?? []);
        }
        return redirect()->back()->with('success', 'Đã cập nhật Ma trận Phân quyền thành công!');
    }
}