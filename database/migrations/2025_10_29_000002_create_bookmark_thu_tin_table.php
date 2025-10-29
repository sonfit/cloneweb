<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmark_thu_tin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bookmark_id')->constrained('bookmarks')->cascadeOnDelete();
            $table->foreignId('thu_tin_id')->constrained('thu_tins')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['bookmark_id', 'thu_tin_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmark_thu_tin');
    }
};


