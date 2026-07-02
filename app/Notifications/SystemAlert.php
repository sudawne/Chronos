<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemAlert extends Notification
{
    use Queueable;
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        // Khai báo lưu vào database
        return ['database']; 
    }

    public function toArray($notifiable)
    {
        // Cấu trúc dữ liệu sẽ được lưu
        return [
            'title'      => $this->data['title'],
            'message'    => $this->data['message'],
            'icon'       => $this->data['icon'] ?? 'notifications',
            'bg_color'   => $this->data['bg_color'] ?? 'bg-indigo-100',
            'text_color' => $this->data['text_color'] ?? 'text-indigo-600',
            'link'       => $this->data['link'] ?? '#'
        ];
    }
}