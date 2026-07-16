<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    private function checkPermission($permissionCode) 
    {
        if (!Auth::check()) abort(403, 'Từ chối truy cập! Vui lòng đăng nhập.');
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
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
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role); 
        }

        $roles = Role::all();
        
        $users = $query->paginate(15)->withQueryString(); 

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function edit(User $user)
    {
        $this->checkPermission('user.edit'); 
        $roles = Role::all();
        $modules = $this->getModules(); 
        
        $this->ensurePermissionsExist($modules);
        
        return view('admin.users.edit', compact('user', 'roles', 'modules'));
    }

    public function update(Request $request, User $user)
    {
        $this->checkPermission('user.edit'); 
        
        $user->syncRoles($request->input('roles', []));
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $user->refresh(); 
        
        $rolePermissions = $user->getPermissionsViaRoles()->pluck('name')->toArray();
        $inputPermissions = $request->input('permissions', []);
        $permissionsToSync = array_diff($inputPermissions, $rolePermissions);
        
        $user->syncPermissions($permissionsToSync);

        if (Auth::id() === $user->id) {
            if (!$user->hasRole('Admin') && !$user->hasPermissionTo('user.view')) {
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
    public function changePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed', 
        ]);

        $user->update([
            'password' => bcrypt($request->password)
        ]);

        return back()->with('success', "Đã cập nhật mật khẩu thành công cho tài khoản {$user->name}");
    }
}