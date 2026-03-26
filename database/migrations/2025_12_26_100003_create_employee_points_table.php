<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Баллы сотрудников (история начислений)
        Schema::create('employee_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('points'); // Может быть отрицательным
            $table->string('source_type', 50); // nomination_win, nomination_given, kpi_bonus, manual, badge
            $table->unsignedBigInteger('source_id')->nullable(); // ID связанной записи
            $table->string('description', 255);
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('source_type');
        });

        // Агрегированные баллы (для быстрого доступа)
        Schema::create('employee_point_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('total_points')->default(0);
            $table->integer('monthly_points')->default(0);
            $table->integer('quarterly_points')->default(0);
            $table->integer('yearly_points')->default(0);
            $table->integer('nominations_received')->default(0);
            $table->integer('nominations_given')->default(0);
            $table->integer('awards_won')->default(0);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->index('total_points');
            $table->index('monthly_points');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_point_balances');
        Schema::dropIfExists('employee_points');
    }
};
