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
        Schema::create('muc_tieus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('Tên hiển thị trong bảng cho dễ tìm kiếm');
            $table->unsignedTinyInteger('type')->comment('Phân loại mục tiêu: face cá nhân, fanpage, group, tiktok, channel tele, group tele,...');
            $table->string('link')->comment('Link đi đến mục tiêu');
            $table->timestamp('time_create')->useCurrent()->comment('Thời gian tạo trên hệ thống');
            $table->timestamp('time_crawl')->nullable()->comment('Thời gian cuối cùng bot truy cập mục tiêu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muc_tieus');
    }
};
