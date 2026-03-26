<?php

namespace App\Enums;

enum PointSourceType: string
{
    case NominationWin = 'nomination_win';
    case NominationGiven = 'nomination_given';
    case AwardWin = 'award_win';
    case KpiBonus = 'kpi_bonus';
    case Manual = 'manual';
    case Badge = 'badge';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::NominationWin => 'Номинацияда ғалаба',
            self::NominationGiven => 'Номинация берилди',
            self::AwardWin => 'Мукофот',
            self::KpiBonus => 'KPI бонус',
            self::Manual => 'Қўлда киритилган',
            self::Badge => 'Бейджик',
            self::Adjustment => 'Тузатиш',
        };
    }

    public function labelRu(): string
    {
        return match ($this) {
            self::NominationWin => 'Победа в номинации',
            self::NominationGiven => 'Номинация выдана',
            self::AwardWin => 'Награда',
            self::KpiBonus => 'KPI бонус',
            self::Manual => 'Ручное начисление',
            self::Badge => 'Значок',
            self::Adjustment => 'Корректировка',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::NominationWin => 'bi-trophy-fill',
            self::NominationGiven => 'bi-hand-thumbs-up-fill',
            self::AwardWin => 'bi-award-fill',
            self::KpiBonus => 'bi-graph-up-arrow',
            self::Manual => 'bi-pencil-fill',
            self::Badge => 'bi-shield-fill-check',
            self::Adjustment => 'bi-sliders',
        };
    }

    public function defaultPoints(): int
    {
        return match ($this) {
            self::NominationWin => 100,
            self::NominationGiven => 10,
            self::AwardWin => 500,
            self::KpiBonus => 50,
            self::Manual => 0,
            self::Badge => 25,
            self::Adjustment => 0,
        };
    }
}
