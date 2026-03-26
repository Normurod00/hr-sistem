<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->json('strengths')->nullable();
            $table->json('weaknesses')->nullable();
            $table->json('risks')->nullable();
            $table->json('suggested_questions')->nullable();
            $table->text('recommendation')->nullable();
            $table->json('raw_ai_payload')->nullable();
            $table->timestamps();

            $table->unique('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_analyses');
    }
};
