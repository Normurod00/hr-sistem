<?php

namespace App\Enums;

enum AiContextType: string
{
    case General = 'general';
    case Kpi = 'kpi';
    case Leave = 'leave';
    case Bonus = 'bonus';
    case Policy = 'policy';
    case Complaint = 'complaint';

    public function label(): string
    {
        return match ($this) {
            self::General => 'Общий вопрос',
            self::Kpi => 'KPI',
            self::Leave => 'Отпуск',
            self::Bonus => 'Бонус',
            self::Policy => 'Политики',
            self::Complaint => 'Обращение',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::General => 'bi-chat-dots',
            self::Kpi => 'bi-graph-up',
            self::Leave => 'bi-calendar-check',
            self::Bonus => 'bi-currency-dollar',
            self::Policy => 'bi-file-text',
            self::Complaint => 'bi-exclamation-triangle',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
