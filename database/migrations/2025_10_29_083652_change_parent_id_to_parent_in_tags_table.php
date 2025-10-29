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
        Schema::table('tags', function (Blueprint $table) {
            // Drop foreign key and parent_id column
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            
            // Add parent column as string
            $table->string('parent')
                ->nullable()
                ->after('id')
                ->comment('Nhóm phân loại (ví dụ: Các tỉnh miền Bắc, Các tỉnh miền Nam...)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            // Drop parent column
            $table->dropColumn('parent');
            
            // Add back parent_id with foreign key
            $table->unsignedBigInteger('parent_id')
                ->nullable()
                ->after('id');
            
            $table->foreign('parent_id')
                ->references('id')
                ->on('tags')
                ->nullOnDelete();
        });
    }
};
