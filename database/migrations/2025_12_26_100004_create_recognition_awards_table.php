<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Награды (Ой ходими, Йил ходими)
        Schema::create('recognition_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nomination_type_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('award_type', ['employee_of_month', 'employee_of_quarter', 'employee_of_year']);
            $table->string('title', 150); // "Ой ходими - Декабрь 2025"
            $table->text('description')->nullable();
            $table->integer('points_awarded')->default(0);
            $table->integer('nominations_count')->default(0); // Сколько номинаций получил
            $table->decimal('kpi_score', 5, 2)->nullable(); // KPI на момент награды
            $table->date('period_start');
            $table->date('period_end');
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_published')->default(false); // Опубликовано на dashboard
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Один победитель в одной категории за период
            $table->unique(['award_type', 'nomination_type_id', 'period_start'], 'unique_award_per_period');

            $table->index(['user_id', 'award_type']);
            $table->index(['period_start', 'period_end']);
            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recognition_awards');
    }
};
