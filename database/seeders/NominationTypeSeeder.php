<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NominationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Энг яхши ходим',
                'name_uz' => 'Энг яхши ходим',
                'name_ru' => 'Лучший сотрудник',
                'slug' => 'best_employee',
                'description' => 'Умумий кўрсаткичлари бўйича энг яхши натижаларга эришган ходим',
                'icon' => 'bi-star-fill',
                'color' => '#FFD700',
                'points_reward' => 500,
                'sort_order' => 1,
            ],
            [
                'name' => 'Энг яхши жамоа аъзоси',
                'name_uz' => 'Энг яхши жамоа аъзоси',
                'name_ru' => 'Лучший командный игрок',
                'slug' => 'best_team_player',
                'description' => 'Жамоада ҳамкорлик ва ўзаро ёрдам кўрсатган ходим',
                'icon' => 'bi-people-fill',
                'color' => '#4CAF50',
                'points_reward' => 400,
                'sort_order' => 2,
            ],
            [
                'name' => 'Энг инновацион ходим',
                'name_uz' => 'Энг инновацион ходим',
                'name_ru' => 'Лучший инноватор',
                'slug' => 'best_innovator',
                'description' => 'Янги ғоялар ва такомиллаштиришлар таклиф қилган ходим',
                'icon' => 'bi-lightbulb-fill',
                'color' => '#2196F3',
                'points_reward' => 450,
                'sort_order' => 3,
            ],
            [
                'name' => 'Энг яхши мижоз хизмати',
                'name_uz' => 'Энг яхши мижоз хизмати',
                'name_ru' => 'Лучший клиентский сервис',
                'slug' => 'best_customer_service',
                'description' => 'Мижозларга юқори даражада хизмат кўрсатган ходим',
                'icon' => 'bi-heart-fill',
                'color' => '#E91E63',
                'points_reward' => 400,
                'sort_order' => 4,
            ],
            [
                'name' => 'Энг тез ривожланувчи',
                'name_uz' => 'Энг тез ривожланувчи',
                'name_ru' => 'Самый быстрорастущий',
                'slug' => 'fastest_growing',
                'description' => 'Қисқа вақт ичида катта ўсиш кўрсатган ходим',
                'icon' => 'bi-graph-up-arrow',
                'color' => '#9C27B0',
                'points_reward' => 350,
                'sort_order' => 5,
            ],
        ];

        foreach ($types as $type) {
            DB::table('nomination_types')->updateOrInsert(
                ['slug' => $type['slug']],
                array_merge($type, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
