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
        Schema::create('tong_hops', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('url')->unique();
            $table->text('raw_text')->nullable();
            $table->text('summary_text')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tong_hops');
    }
};
