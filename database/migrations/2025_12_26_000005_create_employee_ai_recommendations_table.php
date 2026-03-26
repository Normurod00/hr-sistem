<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kpi_snapshot_id')->nullable()->constrained('employee_kpi_snapshots')->nullOnDelete();
            $table->enum('type', ['quick', 'medium', 'long']); // quick=1-2 weeks, medium=1-3 months, long=3-12 months
            $table->unsignedTinyInteger('priority')->default(1); // 1=highest
            $table->string('action', 500);
            $table->string('expected_effect', 255)->nullable();
            $table->decimal('expected_impact', 5, 2)->nullable(); // +5.5% к KPI
            $table->enum('status', ['pending', 'in_progress', 'completed', 'dismissed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['employee_profile_id', 'status']);
            $table->index(['kpi_snapshot_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_ai_recommendations');
    }
};
