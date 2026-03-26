<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_kpi_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->enum('period_type', ['month', 'quarter', 'half_year', 'year']);
            $table->date('period_start');
            $table->date('period_end');
            $table->json('metrics'); // {"sales": {"value": 85, "target": 100, "weight": 0.3}, ...}
            $table->decimal('total_score', 5, 2)->default(0); // 0-100
            $table->enum('status', ['pending', 'calculated', 'approved', 'disputed'])->default('pending');
            $table->json('bonus_info')->nullable(); // {"eligible": true, "amount": 5000, "paid": false}
            $table->timestamp('synced_at')->nullable();
            $table->json('raw_response')->nullable(); // сырой ответ от внешнего API
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_profile_id', 'period_type', 'period_start']);
            $table->index(['period_type', 'period_start', 'period_end']);
            $table->index(['status', 'synced_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_kpi_snapshots');
    }
};
