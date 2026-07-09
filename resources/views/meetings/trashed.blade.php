@extends('layouts.app')

@section('title', 'Thùng rác Sự kiện | CHRONOS')

@section('content')
<div class="px-4 lg:px-8 pb-12">
    <div class="mb-6 pt-6 flex items-center justify-between border-b border-slate-100 dark:border-white/5 pb-4">
        <div>
            <a href="{{ route('meetings.index') }}" class="text-xs text-slate-400 hover:text-indigo-600 mb-2 inline-flex items-center gap-1 font-medium">
                <span class="material-symbols-outlined text-[14px]">arrow_back</span> Quay lại danh sách
            </a>
            <h1 class="text-xl font-bold text-rose-600 uppercase tracking-wide flex items-center gap-2">
                <span class="material-symbols-outlined">delete</span> Thùng rác Sự kiện
            </h1>
            <p class="text-slate-500 text-sm mt-1">Nơi lưu trữ các sự kiện đã bị xóa. Bạn có thể khôi phục hoặc xóa vĩnh viễn.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-600 rounded-lg text-sm font-bold border border-emerald-100 flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">check_circle</span> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-[#151A2D] rounded-xl shadow-sm border border-slate-200 dark:border-white/5 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-white/5">
                    <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase">Tên sự kiện</th>
                    <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase">Ngày xóa</th>
                    <th class="px-6 py-4 text-xs font-black text-slate-500 uppercase text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                @forelse($meetings as $meeting)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-700 dark:text-slate-300">{{ $meeting->title }}</div>
                            <div class="text-xs text-slate-400">Tạo bởi: {{ $meeting->user->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">
                            {{ $meeting->deleted_at->format('H:i d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
                            <form action="{{ route('meetings.restore', $meeting->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="px-3 py-1.5 bg-emerald-100 hover:bg-emerald-600 text-emerald-700 hover:text-white rounded text-xs font-bold transition-colors flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">restore</span> Khôi phục
                                </button>
                            </form>

                            <form action="{{ route('meetings.force_delete', $meeting->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa VĨNH VIỄN sự kiện này? Toàn bộ dữ liệu đại biểu, hình ảnh sẽ mất trắng và không thể khôi phục!');">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white rounded text-xs font-bold transition-colors flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">delete_forever</span> Xóa vĩnh viễn
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-slate-400">
                            <span class="material-symbols-outlined text-4xl mb-2 block opacity-50">recycling</span>
                            Thùng rác trống.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $meetings->links() }}
    </div>
</div>
@endsection