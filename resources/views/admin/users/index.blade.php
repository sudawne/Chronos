@extends('layouts.app')

@section('title', 'Quản lý Tài khoản & Phân quyền')

@section('content')
<div class="max-w-7xl mx-auto p-4 lg:p-8">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600 dark:text-indigo-400">groups</span>
                Danh sách Quản trị viên
            </h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Cấp quyền riêng lẻ cho từng người hoặc cấu hình nhóm</p>
        </div>
        
        <a href="{{ route('admin.matrix.index') }}" 
        data-no-swup
        class="flex items-center gap-2 px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl shadow-sm transition-all font-bold">
            <span class="material-symbols-outlined">grid_view</span> Cập nhật Ma Trận Nhóm
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 p-4 rounded-xl text-emerald-800 dark:text-emerald-400 font-bold text-sm shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Bộ lọc & Tìm kiếm -->
    <div class="mb-6 bg-white dark:bg-[#151A2D] p-4 rounded-2xl shadow-sm border border-slate-200 dark:border-white/5">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3">
            <!-- Tìm kiếm text -->
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm tên, email..." 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-[#1A2235] text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
            </div>
            
            <!-- Lọc theo Role -->
            <div class="sm:w-64">
                <select name="role" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-[#1A2235] text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none transition-all appearance-none">
                    <option value="">Tất cả chức vụ</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Nút submit & clear -->
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm transition-all font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">filter_alt</span> Lọc
                </button>
                @if(request()->hasAny(['search', 'role']))
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl transition-all font-bold flex items-center" title="Xóa lọc">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Bảng dữ liệu -->
    <div class="bg-white dark:bg-[#151A2D] rounded-3xl shadow-sm border border-slate-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="uppercase tracking-wider border-b-2 border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-[#1A2235] text-slate-500 dark:text-slate-400 font-bold">
                    <tr>
                        <th class="px-6 py-4">Thành viên</th>
                        <th class="px-6 py-4">Chức vụ (Roles)</th>
                        <th class="px-6 py-4">Quyền cấp thêm (Riêng)</th>
                        <th class="px-6 py-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-slate-700 dark:text-slate-300">
                    @foreach($users as $u)
                    <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $u->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($u->name) }}" class="w-10 h-10 rounded-full border dark:border-slate-600">
                                <div>
                                    <p class="font-bold text-slate-800 dark:text-slate-100">{{ $u->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $u->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @forelse($u->roles as $role)
                                    <span class="px-2.5 py-1 bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 text-[11px] font-black uppercase rounded-md border border-amber-200 dark:border-amber-500/20">{{ $role->name }}</span>
                                @empty
                                    <span class="text-slate-400 text-xs italic">Chưa có chức vụ</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1 max-w-[250px] whitespace-normal">
                                @forelse($u->getDirectPermissions() as $perm)
                                    <span class="px-2 py-1 bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 text-[11px] font-bold rounded-md border border-indigo-100 inline-block mb-1">{{ $perm->name }}</span>
                                @empty
                                    <span class="text-slate-400 text-xs italic">Không có</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.users.edit', $u->id) }}" data-no-swup class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg font-semibold transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">tune</span> Cấp quyền
                                </a>
                                <!-- Nút gọi Modal Đổi mật khẩu -->
                                <button type="button" onclick="openPasswordModal({{ $u->id }}, '{{ $u->name }}')" class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-rose-50 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg font-semibold transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">key</span> Đổi MK
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Phân trang nếu có -->
        @if(method_exists($users, 'links'))
        <div class="p-4 border-t border-slate-100 dark:border-white/5">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Đổi Mật Khẩu -->
<div id="passwordModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center transition-opacity">
    <div class="bg-white dark:bg-[#151A2D] w-full max-w-md rounded-3xl shadow-xl overflow-hidden transform scale-95 transition-transform" id="passwordModalContent">
        <form id="changePasswordForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="p-6 border-b border-slate-100 dark:border-white/5 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-rose-500">lock_reset</span>
                    Đổi Mật Khẩu
                </h3>
                <button type="button" onclick="closePasswordModal()" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-6">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                    Đang thay đổi mật khẩu cho người dùng: <strong id="modalUserName" class="text-indigo-600 dark:text-indigo-400"></strong>
                </p>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Mật khẩu mới</label>
                        <input type="password" name="password" required minlength="6"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-[#1A2235] text-slate-800 dark:text-white focus:ring-2 focus:ring-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Xác nhận mật khẩu</label>
                        <input type="password" name="password_confirmation" required minlength="6"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-[#1A2235] text-slate-800 dark:text-white focus:ring-2 focus:ring-rose-500 outline-none">
                    </div>
                </div>
            </div>
            
            <div class="p-4 border-t border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-[#1A2235] flex justify-end gap-3">
                <button type="button" onclick="closePasswordModal()" class="px-5 py-2.5 rounded-xl font-bold text-slate-600 hover:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors">
                    Hủy
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-white bg-rose-500 hover:bg-rose-600 shadow-sm transition-all">
                    Lưu Mật Khẩu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPasswordModal(userId, userName) {
        document.getElementById('modalUserName').innerText = userName;
        
        document.getElementById('changePasswordForm').action = `/admin/users/${userId}/change-password`;
        
        const modal = document.getElementById('passwordModal');
        const modalContent = document.getElementById('passwordModalContent');
        modal.classList.remove('hidden');
        
        setTimeout(() => {
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }, 10);
    }

    function closePasswordModal() {
        const modal = document.getElementById('passwordModal');
        const modalContent = document.getElementById('passwordModalContent');
        
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.getElementById('changePasswordForm').reset();
        }, 200);
    }
</script>
@endsection