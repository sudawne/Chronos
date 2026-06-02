@extends('layouts.app')

@section('title', 'Thông tin cá nhân')

@section('content')
<div class="max-w-4xl mx-auto p-4 lg:p-8">
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">
        
        <div class="flex items-center justify-between mb-6 border-b border-slate-100 dark:border-slate-700 pb-5">
            <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400">manage_accounts</span>
                Điều chỉnh thông tin cá nhân
            </h2>
            
            <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                    class="flex items-center gap-1.5 px-4 py-2 bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 rounded-xl font-bold text-sm transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[18px]">logout</span>
                Đăng xuất
            </button>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PATCH')

            <div class="flex flex-col md:flex-row gap-8">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-32 h-32 rounded-full border-4 border-indigo-100 dark:border-slate-700 shadow-md overflow-hidden bg-slate-100 dark:bg-slate-900">
                        <img src="{{ $user->avatar ? asset($user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" class="w-full h-full object-cover">
                    </div>
                    <label class="cursor-pointer px-4 py-2 bg-indigo-50 dark:bg-slate-700 text-indigo-600 dark:text-indigo-300 rounded-xl font-bold text-sm hover:bg-indigo-100 dark:hover:bg-slate-600 transition-colors">
                        Đổi ảnh
                        <input type="file" name="avatar" class="hidden" accept="image/*">
                    </label>
                </div>

                <div class="flex-1 space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-600 dark:text-slate-400 mb-2">Họ và tên</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-3 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-600 dark:text-slate-400 mb-2">Địa chỉ Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-3 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 transition-all outline-none">
                    </div>
                    
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 transition-all">
                            Lưu thay đổi
                        </button>

                        <button type="button" onclick="document.getElementById('logout-form').submit();" 
                                class="px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
                            Đăng xuất tài khoản
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>
@endsection