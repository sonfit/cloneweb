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
        Schema::create('trace_jobs', function (Blueprint $table) {
            $table->id();
            $table->json('payload');
            $table->string('status', 20)->default('pending')->index(); // pending|processing|done|failed
            $table->json('result')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
            
            // Composite index cho status + updated_at
            $table->index(['status', 'updated_at'], 'idx_trace_jobs_status_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trace_jobs');
    }
};
