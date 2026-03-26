<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255)->nullable();
            $table->enum('context_type', ['general', 'kpi', 'leave', 'bonus', 'policy', 'complaint']);
            $table->enum('status', ['active', 'closed', 'archived'])->default('active');
            $table->json('metadata')->nullable(); // {"related_kpi_id": 123, ...}
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['employee_profile_id', 'status']);
            $table->index(['context_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_ai_conversations');
    }
};
