<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('employee_ai_conversations')->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->text('content');
            $table->string('intent', 50)->nullable(); // leave_request, kpi_question, bonus_inquiry, policy_search
            $table->json('metadata')->nullable(); // {"confidence": 0.95, "sources": [...]}
            $table->unsignedInteger('tokens_used')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index('intent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_ai_messages');
    }
};
