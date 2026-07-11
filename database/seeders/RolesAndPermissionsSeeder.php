<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // 1. Dọn dẹp cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Danh sách các quyền chính xác như trong web.php của bạn
        $permissions = [
            'admin.users', 
            'admin.matrix',
            'meeting.view', 
            'meeting.create', 
            'meeting.edit', 
            'meeting.delete', 
            'meeting.force_delete',
            'meeting.design', 
            'template.manage',
            'ai.validate_faces', 
            'ai.server_control',
            'attendance.scan_qr', 
            'attendance.manage', 
            'attendance.gate_monitor', 
            'attendance.export'
        ];

        // Tạo quyền trong DB
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 3. Tạo các Vai trò (Roles)
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $eventManagerRole = Role::firstOrCreate(['name' => 'Event Manager', 'guard_name' => 'web']);
        $aiOperatorRole = Role::firstOrCreate(['name' => 'AI Operator', 'guard_name' => 'web']);
        $receptionRole = Role::firstOrCreate(['name' => 'Reception Staff', 'guard_name' => 'web']);

        // 4. Phân bổ quyền cho các vai trò
        // Reception Staff (Lễ tân)
        $receptionRole->syncPermissions([
            'meeting.view', 'attendance.scan_qr', 'attendance.export'
        ]);

        // AI Operator (Kỹ thuật AI)
        $aiOperatorRole->syncPermissions([
            'meeting.view', 'ai.validate_faces', 'ai.server_control', 'attendance.gate_monitor'
        ]);

        // Event Manager (Quản lý)
        $eventManagerRole->syncPermissions([
            'meeting.view', 'meeting.create', 'meeting.edit', 'meeting.delete',
            'meeting.design', 'template.manage', 
            'attendance.manage', 'attendance.export'
        ]);

        // Siêu Quản Trị (Full quyền)
        $adminRole->syncPermissions(Permission::all());

        // 5. [QUAN TRỌNG NHẤT] Ép cứng quyền Admin cho tài khoản của bạn (ID = 1)
        // Nếu ID của bạn không phải là 1, hãy đổi số 1 thành ID tương ứng.
        $user = User::find(1);
        if ($user) {
            $user->assignRole($adminRole);
            $this->command->info('Đã cấp quyền Admin cho tài khoản ID: 1');
        } else {
            $this->command->error('Không tìm thấy User ID 1 để cấp quyền!');
        }
    }
}