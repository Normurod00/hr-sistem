<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('operation', 50); // parse_resume, analyze, match_score, etc.
            $table->enum('status', ['pending', 'success', 'error'])->default('pending');
            $table->unsignedInteger('duration_ms')->nullable(); // Время выполнения в мс
            $table->text('message')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('operation');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
    }
};
