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
        Schema::table('thu_tins', function (Blueprint $table) {

            $table->unsignedBigInteger('id_muctieu')
                ->after('id_user')
                ->nullable()
                ->comment('ID muc tieu (liên kết với bảng muc tieu)');
            $table->foreign('id_muctieu')
                ->references('id')
                ->on('muc_tieus')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thu_tins', function (Blueprint $table) {
            $table->dropColumn('id_muctieu');
        });
    }
};
