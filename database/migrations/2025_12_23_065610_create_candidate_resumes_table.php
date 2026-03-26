<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('candidate_resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('set null');

            // Личные данные
            $table->string('full_name');
            $table->date('birth_date')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->string('citizenship')->nullable();

            // Желаемая должность
            $table->string('desired_position')->nullable();
            $table->string('desired_salary', 100)->nullable();

            // Структурированные данные (JSON)
            $table->json('education')->nullable();
            $table->json('experience')->nullable();
            $table->text('skills')->nullable();
            $table->json('languages')->nullable();

            // О себе
            $table->text('about')->nullable();

            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_resumes');
    }
};
