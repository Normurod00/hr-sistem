<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Типы номинаций (настраиваемые HR)
        Schema::create('nomination_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // "Энг яхши ходим"
            $table->string('name_uz', 100)->nullable(); // Узбекча
            $table->string('name_ru', 100)->nullable(); // Русча
            $table->string('slug', 50)->unique(); // best_employee
            $table->text('description')->nullable();
            $table->string('icon', 50)->default('bi-star-fill'); // Bootstrap icon
            $table->string('color', 20)->default('#FFD700'); // Gold
            $table->integer('points_reward')->default(100); // Балл за победу
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomination_types');
    }
};
