@extends('layouts.app')

@section('title', 'Phân quyền Cá nhân | CHRONOS')

@section('content')
<style>
    /* CSS Tùy chỉnh Checkbox giống hệt Base.vn */
    .base-checkbox {
        appearance: none;
        width: 20px; height: 20px;
        border: 1px solid #d1d5db; border-radius: 4px;
        background-color: #fff; position: relative; cursor: pointer;
        transition: all 0.2s ease;
    }
    .base-checkbox:hover { border-color: #22c55e; }
    .base-checkbox:checked { background-color: #22c55e; border-color: #22c55e; }
    .base-checkbox:checked::after {
        content: ''; position: absolute; left: 6px; top: 2px;
        width: 6px; height: 11px;
        border: solid white; border-width: 0 2px 2px 0;
        transform: rotate(45deg);
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
            <p class="text-gray-500 text-sm mt-1">Gán chức vụ (Quyền to) và cấp các tính năng bổ sung (Quyền nhỏ) cho nhân sự này.</p>
        </div>
        
        <button type="button" onclick="document.getElementById('user-permission-form').submit();" class="px-6 py-2 bg-purple-600 text-white rounded font-medium hover:bg-purple-700 transition-colors shadow-sm text-sm">
            Lưu thay đổi
        </button>
    </div>

    <form id="user-permission-form" action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf @method('PUT')
        
        {{-- PHẦN 1: QUYỀN TO (CHỨC VỤ) --}}
        <div class="mb-10">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 border-l-4 border-purple-500 pl-3">
                1. Gán Chức Vụ (Role)
            </h3>
            <table class="w-full text-left border-collapse">
                <tbody class="divide-y divide-gray-100">
                    @foreach($roles as $role)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-4 pr-6 w-1/2">
                            <div class="text-[14px] font-bold text-gray-800 mb-0.5 uppercase">{{ $role->name }}</div>
                            <div class="text-[12px] text-gray-400">Áp dụng bộ phân quyền mặc định của nhóm {{ $role->name }}</div>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'checked' : '' }} class="base-checkbox inline-block">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- PHẦN 2: QUYỀN NHỎ (QUYỀN LẺ TRỰC TIẾP) --}}
        <div>
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2 border-l-4 border-green-500 pl-3">
                2. Cấp thêm tính năng lẻ (Direct Permissions)
            </h3>
            <p class="text-[12px] text-gray-400 mb-4 pl-4">Nếu tính năng đã được bao gồm trong Chức vụ ở trên, bạn không cần phải tích thêm ở đây.</p>
            
            <table class="w-full text-left border-collapse border-t border-gray-100">
                <tbody class="divide-y divide-gray-100">
                    @foreach($permissions as $permission)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-4 pr-6 w-1/2">
                            <div class="text-[14px] font-bold text-gray-700 mb-0.5">{{ $permission->name }}</div>
                            <div class="text-[12px] text-gray-400">Quyền truy cập độc lập vào hệ thống</div>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" {{ $user->hasDirectPermission($permission->name) ? 'checked' : '' }} class="base-checkbox inline-block">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
</div>
@endsection