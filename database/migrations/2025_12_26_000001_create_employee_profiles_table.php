<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('employee_number', 50)->unique();
            $table->string('department', 100);
            $table->string('position', 150);
            $table->foreignId('manager_id')->nullable()->constrained('employee_profiles')->nullOnDelete();
            $table->enum('role', ['employee', 'manager', 'hr', 'sysadmin'])->default('employee');
            $table->date('hire_date');
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated'])->default('active');
            $table->string('phone_internal', 20)->nullable();
            $table->string('office_location', 100)->nullable();
            $table->json('metadata')->nullable(); // дополнительные данные
            $table->timestamps();
            $table->softDeletes();

            $table->index(['department', 'status']);
            $table->index(['manager_id', 'status']);
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};
