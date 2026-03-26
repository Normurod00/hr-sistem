<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Номинации (кто кого номинировал)
        Schema::create('nominations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nomination_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nominee_id')->constrained('users')->cascadeOnDelete(); // Кого номинируют
            $table->foreignId('nominator_id')->constrained('users')->cascadeOnDelete(); // Кто номинирует
            $table->text('reason'); // Причина номинации
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_comment')->nullable();
            $table->string('period_type', 20)->default('month'); // month, quarter, year
            $table->date('period_start'); // Начало периода
            $table->date('period_end'); // Конец периода
            $table->timestamps();

            // Один человек может номинировать другого только раз за период в одной категории
            $table->unique(['nomination_type_id', 'nominee_id', 'nominator_id', 'period_start'], 'unique_nomination_per_period');

            $table->index(['nominee_id', 'status']);
            $table->index(['period_start', 'period_end']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nominations');
    }
};
