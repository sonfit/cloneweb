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
        Schema::create('user_muc_tieu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('muc_tieu_id');
            $table->timestamps();
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->foreign('muc_tieu_id')
                ->references('id')
                ->on('muc_tieus')
                ->onDelete('cascade');
            
            // Đảm bảo mỗi user chỉ theo dõi mục tiêu một lần
            $table->unique(['user_id', 'muc_tieu_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_muc_tieu');
    }
};
