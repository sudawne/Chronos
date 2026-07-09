@extends('layouts.app')

@section('title', 'Phân quyền Cá nhân | CHRONOS')

@section('content')
<style>
    .base-checkbox {
        appearance: none; width: 20px; height: 20px;
        border: 1px solid #d1d5db; border-radius: 4px;
        background-color: #fff; position: relative; cursor: pointer; transition: all 0.2s ease;
    }
    .base-checkbox:hover { border-color: #22c55e; }
    .base-checkbox:checked { background-color: #22c55e; border-color: #22c55e; }
    .base-checkbox:checked::after {
        content: ''; position: absolute; left: 6px; top: 2px;
        width: 6px; height: 11px;
        border: solid white; border-width: 0 2px 2px 0; transform: rotate(45deg);
    }
    .base-checkbox:focus { outline: none; box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2); }
</style>

<div class="px-4 lg:px-8 pb-12 min-h-screen bg-white">
    
    <div class="mb-6 pt-6 flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <a href="{{ route('admin.users.index') }}" class="text-xs text-gray-400 hover:text-purple-600 mb-2 inline-flex items-center gap-1 font-medium">
                <span class="material-symbols-outlined text-[14px]">arrow_back</span> Quay lại danh sách
            </a>
            <h1 class="text-xl font-bold text-gray-800 uppercase tracking-wide flex items-center gap-3">
                <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" class="w-8 h-8 rounded border border-gray-200">
                Phân quyền: {{ $user->name }}
            </h1>
            <p class="text-gray-500 text-sm mt-1">Gán chức vụ và cấp các tính năng bổ sung cho nhân sự này.</p>
        </div>
        
        <button type="button" onclick="document.getElementById('user-permission-form').submit();" class="px-6 py-2 bg-purple-600 text-white rounded font-medium hover:bg-purple-700 transition-colors shadow-sm text-sm">
            Lưu thay đổi
        </button>
    </div>

    <form id="user-permission-form" action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf @method('PUT')
        
        {{-- PHẦN 1: QUYỀN TO (CHỨC VỤ ACT AS TEMPLATE) --}}
        <div class="mb-10">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 border-l-4 border-purple-500 pl-3">
                1. Gán Chức Vụ
            </h3>
            <table class="w-full text-left border-collapse border border-gray-100 rounded-lg overflow-hidden">
                <tbody class="divide-y divide-gray-100">
                    @foreach($roles as $role)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-4 px-6 w-1/2 border-l-2 border-transparent hover:border-purple-500">
                            <div class="text-[14px] font-bold text-gray-800 mb-0.5 uppercase">{{ $role->name }}</div>
                            <div class="text-[12px] text-gray-400">Áp dụng bộ phân quyền mặc định của nhóm {{ $role->name }}</div>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                   {{ $user->hasRole($role->name) ? 'checked' : '' }} 
                                   class="role-checkbox base-checkbox inline-block">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PHẦN 2: QUYỀN NHỎ --}}
        <div>
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2 border-l-4 border-green-500 pl-3">
                2. Tính năng độc lập
            </h3>
            <div class="border border-gray-100 rounded-lg overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <tbody class="divide-y divide-gray-100">
                        @foreach($modules as $moduleName => $actions)
                            {{-- Tiêu đề Phân khu --}}
                            <tr class="bg-gray-50/50">
                                <td colspan="2" class="py-3 px-6 text-[11px] font-bold text-gray-500 uppercase tracking-wider bg-gray-100/50">
                                    {{ $moduleName }}
                                </td>
                            </tr>

                            {{-- Các quyền chi tiết --}}
                            @foreach($actions as $actionLabel => $permissionCode)
                                <tr class="perm-row hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 w-1/2 pl-8 border-l-2 border-transparent hover:border-green-500">
                                        <div class="text-[14px] font-bold text-gray-700 mb-0.5 flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                            {{ $actionLabel }}
                                        </div>
                                        <div class="text-[12px] text-gray-400 ml-3.5">Mã hệ thống: {{ $permissionCode }}</div>
                                    </td>
                                    
                                    <td class="py-4 px-6 text-right">
                                        <input type="checkbox" name="permissions[]" value="{{ $permissionCode }}" 
                                            {{ $user->hasDirectPermission($permissionCode) ? 'checked' : '' }} 
                                            class="perm-checkbox base-checkbox inline-block"
                                            data-direct-checked="{{ $user->hasDirectPermission($permissionCode) ? 'true' : 'false' }}">
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

{{-- SCRIPT ĐỒNG BỘ THÔNG MINH (TEMPLATE MODE) --}}
<script>
    const rolePermsMap = {
        @foreach($roles as $role)
            "{{ $role->name }}": {!! json_encode($role->permissions->pluck('name')) !!},
        @endforeach
    };

    document.addEventListener('DOMContentLoaded', () => {
        const roleCheckboxes = document.querySelectorAll('.role-checkbox');
        const permCheckboxes = document.querySelectorAll('.perm-checkbox');

        // --- [HÀM MỚI]: ĐỒNG BỘ NGAY KHI VỪA MỞ TRANG ---
        function syncOnLoad() {
            roleCheckboxes.forEach(roleCb => {
                // Nếu thấy Chức vụ đã được tích sẵn từ Database
                if (roleCb.checked) {
                    const roleName = roleCb.value;
                    const permsForThisRole = rolePermsMap[roleName] || [];
                    
                    // Thì tự động tích các quyền nhỏ tương ứng bên dưới
                    permsForThisRole.forEach(p => {
                        const permCb = document.querySelector(`.perm-checkbox[value="${p}"]`);
                        if (permCb) permCb.checked = true;
                    });
                }
            });
        }

        // KÍCH HOẠT HÀM ĐỒNG BỘ LẦN ĐẦU TIÊN
        syncOnLoad();

        // 1. KHI NGƯỜI DÙNG BẤM VÀO CHỨC VỤ (ROLE)
        roleCheckboxes.forEach(roleCb => {
            roleCb.addEventListener('change', function() {
                const roleName = this.value;
                const permsForThisRole = rolePermsMap[roleName] || [];

                if (this.checked) {
                    permsForThisRole.forEach(p => {
                        const permCb = document.querySelector(`.perm-checkbox[value="${p}"]`);
                        if (permCb) permCb.checked = true;
                    });
                } else {
                    permsForThisRole.forEach(p => {
                        const permCb = document.querySelector(`.perm-checkbox[value="${p}"]`);
                        if (permCb) {
                            let keepChecked = false;
                            roleCheckboxes.forEach(otherRoleCb => {
                                if (otherRoleCb !== this && otherRoleCb.checked) {
                                    if (rolePermsMap[otherRoleCb.value].includes(p)) keepChecked = true;
                                }
                            });
                            if (!keepChecked) permCb.checked = false;
                        }
                    });
                }
            });
        });

        // 2. KHI NGƯỜI DÙNG BỎ TÍCH 1 QUYỀN (BIẾN THÀNH CUSTOM USER)
        permCheckboxes.forEach(permCb => {
            permCb.addEventListener('change', function() {
                if (!this.checked) {
                    const permName = this.value;
                    roleCheckboxes.forEach(roleCb => {
                        if (roleCb.checked && rolePermsMap[roleCb.value].includes(permName)) {
                            roleCb.checked = false; 
                        }
                    });
                }
            });
        });
    });
</script>
@endsection