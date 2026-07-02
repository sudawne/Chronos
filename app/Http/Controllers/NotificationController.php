<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // API lấy danh sách thông báo chưa đọc
    public function getUnread()
    {
        if (!Auth::check()) {
            return response()->json(['unread_count' => 0, 'notifications' => []]);
        }

        $user = Auth::user();
        $unreadNotifications = $user->unreadNotifications;

        $formatted = $unreadNotifications->map(function ($notif) {
            return [
                'id'         => $notif->id,
                'title'      => $notif->data['title'] ?? 'Hệ thống',
                'message'    => $notif->data['message'] ?? '',
                'time_ago'   => $notif->created_at->diffForHumans(),
                'icon'       => $notif->data['icon'] ?? 'notifications',
                'bg_color'   => $notif->data['bg_color'] ?? 'bg-indigo-100',
                'text_color' => $notif->data['text_color'] ?? 'text-indigo-600',
                'link'       => $notif->data['link'] ?? '#',
                'is_read'    => false,
            ];
        });

        return response()->json([
            'unread_count' => $unreadNotifications->count(),
            'notifications' => $formatted
        ]);
    }

    // API đánh dấu đã đọc
    public function markAllAsRead()
    {
        if (Auth::check()) {
            Auth::user()->unreadNotifications->markAsRead();
        }
        return response()->json(['status' => 'success']);
    }
}