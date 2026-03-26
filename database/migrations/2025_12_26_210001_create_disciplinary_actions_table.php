<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplinary_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->onDelete('cascade');

            // Тип и причина
            $table->string('type', 50); // warning, reprimand, fine, suspension, termination
            $table->string('severity', 20)->default('minor'); // minor, moderate, major, critical
            $table->string('category', 50); // attendance, performance, conduct, policy_violation

            $table->string('title');
            $table->text('description');
            $table->text('reason');

            // Даты
            $table->date('incident_date');
            $table->date('action_date');
            $table->date('effective_from');
            $table->date('effective_until')->nullable();

            // Финансовое воздействие
            $table->decimal('fine_amount', 12, 2)->nullable();
            $table->string('fine_currency', 3)->default('UZS');

            // Статус
            $table->string('status', 20)->default('active'); // draft, active, appealed, revoked, expired

            // Апелляция
            $table->boolean('can_appeal')->default(true);
            $table->date('appeal_deadline')->nullable();
            $table->text('appeal_text')->nullable();
            $table->timestamp('appealed_at')->nullable();
            $table->string('appeal_status', 20)->nullable(); // pending, approved, rejected
            $table->text('appeal_resolution')->nullable();

            // Ответственные
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();

            // Уведомления
            $table->boolean('employee_notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->boolean('employee_acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_profile_id', 'status']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_actions');
    }
};
