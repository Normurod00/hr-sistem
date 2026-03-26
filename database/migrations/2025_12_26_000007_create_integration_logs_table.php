<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('integration', ['kpi', 'pulse', 'smart_office', 'iabs', 'ai_server']);
            $table->string('operation', 100); // sync_kpi, get_employee, health_check
            $table->enum('status', ['pending', 'success', 'error', 'timeout']);
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->string('correlation_id', 36)->nullable(); // UUID для трассировки
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['integration', 'status', 'created_at']);
            $table->index('correlation_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
    }
};
