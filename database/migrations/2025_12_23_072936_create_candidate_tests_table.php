<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Таблица тестов кандидатов
        Schema::create('candidate_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vacancy_id')->constrained()->onDelete('cascade');

            // Вопросы и ответы в JSON
            $table->json('questions')->nullable(); // [{question, options, correct_answer, difficulty, user_answer}]

            // Результаты
            $table->integer('total_questions')->default(15);
            $table->integer('correct_answers')->nullable();
            $table->integer('score')->nullable(); // Процент правильных ответов

            // Время
            $table->integer('time_limit')->default(900); // 15 минут в секундах
            $table->integer('time_spent')->nullable(); // Время потраченное на тест
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Статус
            $table->enum('status', ['pending', 'in_progress', 'completed', 'expired'])->default('pending');

            $table->timestamps();

            $table->index(['application_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        // История SMS уведомлений
        Schema::create('sms_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('set null');
            $table->string('phone', 20);
            $table->text('message');
            $table->enum('type', ['status_change', 'test_reminder', 'interview_invite', 'other'])->default('other');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_notifications');
        Schema::dropIfExists('candidate_tests');
    }
};
