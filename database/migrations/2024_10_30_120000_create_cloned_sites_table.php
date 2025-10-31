<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cloned_sites', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->string('web_clone');
            $table->json('string_replace_arr')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('cloned_sites');
    }
};
