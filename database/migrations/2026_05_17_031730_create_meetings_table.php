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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Tên sự kiện
            $table->dateTime('start_time'); // Thời gian bắt đầu
            $table->dateTime('end_time'); // Thời gian kết thúc
            $table->string('location'); // Địa điểm
            $table->text('description')->nullable(); // Nội dung
            $table->float('recognition_threshold')->default(0.55);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
