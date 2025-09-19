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
        Schema::create('thu_tins', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_bot')
                ->nullable()
                ->comment('ID Bot, có thể để trống nếu chưa xác định');

            $table->unsignedBigInteger('id_user')
                ->nullable()
                ->comment('ID user chia sẻ thông tin (liên kết với bảng users)');

            $table->string('link', 150)
                ->comment('Link bài viết (lược bỏ phần thừa để check trùng lặp)');

            $table->text('contents_text')
                ->nullable()
                ->comment('Nội dung bài viết');

            $table->string('pic', 150)
                ->nullable()
                ->comment('Ảnh chụp màn hình');

            $table->unsignedTinyInteger('phanloai')
                ->nullable()
                ->comment('Phân loại tin tức: ANQG/TTXH...');

            $table->tinyInteger('level')
                ->default(1)
                ->comment('Mưc độ quan trọng của thu tin, từ 1-5. 5 là lớn nhất');

            $table->timestamp('time')->nullable()->comment('Thời gian ghi nhận chia sẻ');

            $table->timestamps();

            $table->foreign('id_user')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('id_bot')
                ->references('id')
                ->on('bots')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thu_tins');
    }
};
