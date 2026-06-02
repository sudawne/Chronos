<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
{
    // 1. Tạo tài khoản Ban tổ chức (Admin)
    User::create([
        'name' => 'Khánh Nguyên (Admin)',
        'email' => 'admin@chronos.com',
        'password' => Hash::make('12345678'), 
    ]);

    // 2. Tạo một sự kiện chạy thử
    Event::create([
        'title' => 'Hội nghị Công nghệ AI 2026',
        'location' => 'Hội trường A - Đại học Kiên Giang',
        'start_time' => now()->addDays(2), // Diễn ra sau 2 ngày
        'end_time' => now()->addDays(2)->addHours(4),
        'status' => 'upcoming',
        'created_by' => 1,
    ]);
}
}
