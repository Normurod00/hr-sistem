<?php

namespace App\Enums;

enum RecommendationType: string
{
    case Quick = 'quick';      // 1-2 недели
    case Medium = 'medium';    // 1-3 месяца
    case Long = 'long';        // 3-12 месяцев

    public function label(): string
    {
        return match ($this) {
            self::Quick => 'Быстрое (1-2 недели)',
            self::Medium => 'Среднее (1-3 месяца)',
            self::Long => 'Долгосрочное (3-12 месяцев)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Quick => 'success',
            self::Medium => 'warning',
            self::Long => 'info',
        };
    }

    public function estimatedDays(): array
    {
        return match ($this) {
            self::Quick => [7, 14],
            self::Medium => [30, 90],
            self::Long => [90, 365],
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
