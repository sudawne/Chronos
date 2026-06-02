@extends('layouts.app')

@section('title', 'Cấp quyền tài khoản')

@section('content')
<div class="max-w-4xl mx-auto p-4 lg:p-8">
    
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-1 text-slate-500 hover:text-indigo-600 font-semibold transition-colors">
            <span class="material-symbols-outlined">arrow_back</span> Quay lại danh sách
        </a>
    </div>

    <div class="bg-white dark:bg-[#151A2D] rounded-3xl shadow-sm border border-slate-200 dark:border-white/5 p-8">
        
        <div class="flex items-center gap-4 mb-8 pb-8 border-b border-slate-100 dark:border-white/5">
            <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" class="w-16 h-16 rounded-full object-cover border-2 border-indigo-100">
            <div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ $user->name }}</h2>
                <p class="text-slate-500">{{ $user->email }}</p>
            </div>
        </div>

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf @method('PUT')
            
            {{-- CHỌN NHÓM CHỨC VỤ --}}
            <h3 class="text-lg font-bold text-amber-600 dark:text-amber-400 mb-4 flex items-center gap-2 uppercase tracking-wide">
                <span class="material-symbols-outlined">badge</span>
                1. Gán Chức vụ (Roles)
            </h3>
            <div class="flex flex-wrap gap-4 mb-10 bg-amber-50 dark:bg-amber-900/10 p-5 rounded-2xl border border-amber-100 dark:border-amber-500/20">
                @foreach($roles as $role)
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'checked' : '' }} class="w-5 h-5 rounded border-amber-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                    <span class="text-sm font-black text-amber-800 dark:text-amber-200">{{ $role->name }}</span>
                </label>
                @endforeach
            </div>

            {{-- CẤP QUYỀN RIÊNG LẺ --}}
            <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mb-4 flex items-center gap-2 uppercase tracking-wide">
                <span class="material-symbols-outlined">verified_user</span>
                2. Cấp thêm tính năng lẻ (Trực tiếp)
            </h3>
            <p class="text-xs text-slate-500 mb-4">Các tính năng dưới đây nếu đã được cấp thông qua "Chức vụ" ở trên thì bạn không cần phải tích lại nữa.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 dark:bg-[#1A2235] p-5 rounded-2xl border border-slate-200 dark:border-white/5">
                @foreach($permissions as $permission)
                <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl border border-transparent hover:border-indigo-200 hover:bg-white dark:hover:bg-slate-800 transition-all shadow-sm">
                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" {{ $user->hasDirectPermission($permission->name) ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $permission->name }}</span>
                </label>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg hover:bg-indigo-700 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">save</span> Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection