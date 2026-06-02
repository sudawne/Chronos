<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->onDelete('cascade');
            
            $table->string('full_name'); // Họ tên khách mời
            $table->string('email')->nullable(); // Email (có thể null)
            $table->string('position')->nullable(); // Chức vụ (VD: Trưởng phòng, Giám đốc...)
            $table->string('seat_location')->nullable(); // Vị trí chỗ ngồi (VD: Hàng A - Ghế 01)
            
            $table->string('image_filename')->nullable(); // Tên file ảnh (hoặc avatar_path)
            
            // Dùng binary để lưu an toàn vector BLOB từ Python gửi sang
            $table->binary('face_vector')->nullable(); 
            
            $table->boolean('is_attended')->default(false); // Trạng thái điểm danh
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
