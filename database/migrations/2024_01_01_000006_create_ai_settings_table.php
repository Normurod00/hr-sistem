<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Добавляем дефолтные настройки
        $this->seedDefaults();
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }

    private function seedDefaults(): void
    {
        $defaults = [
            [
                'key' => 'auto_analyze_on_new_application',
                'value' => json_encode(true),
                'description' => 'Автоматически анализировать новые заявки',
            ],
            [
                'key' => 'generate_strengths',
                'value' => json_encode(true),
                'description' => 'Генерировать сильные стороны',
            ],
            [
                'key' => 'generate_weaknesses',
                'value' => json_encode(true),
                'description' => 'Генерировать слабые стороны',
            ],
            [
                'key' => 'generate_risks',
                'value' => json_encode(true),
                'description' => 'Генерировать риски',
            ],
            [
                'key' => 'generate_questions',
                'value' => json_encode(true),
                'description' => 'Генерировать вопросы для интервью',
            ],
            [
                'key' => 'min_match_score_for_shortlist',
                'value' => json_encode(60),
                'description' => 'Минимальный match score для shortlist',
            ],
        ];

        foreach ($defaults as $setting) {
            \DB::table('ai_settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
};
