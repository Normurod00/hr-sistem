<?php

namespace App\Enums;

enum KpiPeriodType: string
{
    case Month = 'month';
    case Quarter = 'quarter';
    case HalfYear = 'half_year';
    case Year = 'year';

    public function label(): string
    {
        return match ($this) {
            self::Month => 'Месяц',
            self::Quarter => 'Квартал',
            self::HalfYear => 'Полугодие',
            self::Year => 'Год',
        };
    }

    public function months(): int
    {
        return match ($this) {
            self::Month => 1,
            self::Quarter => 3,
            self::HalfYear => 6,
            self::Year => 12,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
