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
            $table->json('pic')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thu_tins', function (Blueprint $table) {
            $table->string('pic', 150)->nullable()->change();

        });
    }
};
