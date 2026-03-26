<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vacancy_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['new', 'in_review', 'invited', 'rejected', 'hired'])->default('new');
            $table->unsignedTinyInteger('match_score')->nullable();
            $table->string('source', 50)->nullable();
            $table->text('notes')->nullable();
            $table->text('cover_letter')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'vacancy_id']);
            $table->index('status');
            $table->index('match_score');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
