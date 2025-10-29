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
            // Drop the level column
            $table->dropColumn('level');
            
            // Add the diem column
            $table->integer('diem')
                ->default(0)
                ->comment('Tổng điểm từ keywords');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thu_tins', function (Blueprint $table) {
            // Drop the diem column
            $table->dropColumn('diem');
            
            // Add back the level column
            $table->tinyInteger('level')
                ->default(1)
                ->comment('Mưc độ quan trọng của thu tin, từ 1-5. 5 là lớn nhất');
        });
    }
};
