@extends('layouts.app')

@section('title', 'Ma Trận Phân Quyền | CHRONOS')

@section('content')
<div class="px-4 lg:px-8 pb-12 min-h-screen relative">
    
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400 text-4xl">admin_panel_settings</span>
                Ma Trận Phân Quyền
            </h1>
            <p class="text-slate-500 dark:text-slate-400 mt-2">Quản lý chi tiết quyền hạn cho từng nhóm tài khoản trên hệ thống.</p>
        </div>
        
        <button type="button" onclick="document.getElementById('matrix-form').submit();" class="flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-xl shadow-sm hover:bg-indigo-700 hover:shadow-md transition-all font-bold">
            <span class="material-symbols-outlined">save</span>
            Cập nhật Phân quyền
        </button>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 p-4 rounded-xl text-emerald-800 dark:text-emerald-400 font-bold text-sm flex items-center gap-3 shadow-sm">
            <span class="material-symbols-outlined">check_circle</span> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-[#151A2D] rounded-2xl shadow-sm border border-slate-200 dark:border-white/5 overflow-hidden">
        
        <form id="matrix-form" action="{{ route('admin.permissions.matrix.update') }}" method="POST">
            @csrf
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-[#1A2235] border-b border-slate-200 dark:border-white/10">
                            <th class="px-6 py-5 text-sm font-black text-slate-700 dark:text-slate-300 w-1/4">Tính năng</th>
                            
                            {{-- IN CÁC CỘT ROLE --}}
                            @foreach($roles as $role)
                                <th class="px-6 py-5 text-sm font-black text-center text-indigo-700 dark:text-indigo-400 uppercase tracking-wide border-l border-slate-200 dark:border-white/5">
                                    {{ $role->name }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        
                        @foreach($modules as $moduleName => $actions)
                            {{-- DÒNG TIÊU ĐỀ MODULE (VD: Tài khoản Admin) --}}
                            <tr class="bg-slate-50/50 dark:bg-white/[0.02]">
                                <td colspan="{{ count($roles) + 1 }}" class="px-6 py-4 text-sm font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                                    {{ $moduleName }}
                                </td>
                            </tr>

                            {{-- CÁC DÒNG HÀNH ĐỘNG BÊN DƯỚI (VD: Xem, Thêm, Sửa, Xóa) --}}
                            @foreach($actions as $actionLabel => $permissionCode)
                                <tr class="hover:bg-slate-50 dark:hover:bg-white/[0.01] transition-colors">
                                    <td class="px-6 py-3.5 text-sm font-medium text-slate-600 dark:text-slate-400 pl-10 flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                        {{ $actionLabel }}
                                    </td>
                                    
                                    {{-- IN CÁC CHECKBOX CHO TỪNG ROLE --}}
                                    @foreach($roles as $role)
                                        <td class="px-6 py-3.5 text-center border-l border-slate-100 dark:border-white/5 relative">
                                            <label class="absolute inset-0 flex items-center justify-center cursor-pointer hover:bg-slate-100/50 dark:hover:bg-white/5 transition-colors">
                                                <input type="checkbox" 
                                                       name="roles[{{ $role->name }}][]" 
                                                       value="{{ $permissionCode }}"
                                                       {{ $role->hasPermissionTo($permissionCode) ? 'checked' : '' }}
                                                       class="w-5 h-5 rounded-[4px] border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 dark:bg-slate-800 cursor-pointer transition-all">
                                            </label>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach

                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>
@endsection