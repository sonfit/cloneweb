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
        Schema::table('dang_kies', function (Blueprint $table) {
            $table->integer('xe_may_dien_muc_3')->default(0)->after('oto_muc_3');
            $table->integer('xe_may_dien_muc_4')->default(0)->after('oto_muc_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dang_kies', function (Blueprint $table) {
            //
        });
    }
};
