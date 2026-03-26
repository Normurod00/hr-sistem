<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->json('must_have_skills')->nullable();
            $table->json('nice_to_have_skills')->nullable();
            $table->decimal('min_experience_years', 4, 1)->nullable();
            $table->json('language_requirements')->nullable();
            $table->unsignedInteger('salary_min')->nullable();
            $table->unsignedInteger('salary_max')->nullable();
            $table->string('location')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'remote', 'internship', 'freelance'])->default('full_time');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('is_active');
            $table->index('employment_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
