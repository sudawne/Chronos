<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('welcome_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên mẫu (VD: Mẫu Đại Hội Đảng)
            $table->longText('config'); // Chứa toàn bộ cấu hình JSON (màu nền, chữ, khung ảnh...)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welcome_templates');
    }
};
