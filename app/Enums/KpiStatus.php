<?php

namespace App\Enums;

enum KpiStatus: string
{
    case Pending = 'pending';
    case Calculated = 'calculated';
    case Approved = 'approved';
    case Disputed = 'disputed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает расчёта',
            self::Calculated => 'Рассчитан',
            self::Approved => 'Утверждён',
            self::Disputed => 'Оспаривается',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'secondary',
            self::Calculated => 'info',
            self::Approved => 'success',
            self::Disputed => 'warning',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
