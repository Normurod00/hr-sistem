<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case OnLeave = 'on_leave';
    case Terminated = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Активный',
            self::Inactive => 'Неактивный',
            self::OnLeave => 'В отпуске',
            self::Terminated => 'Уволен',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'secondary',
            self::OnLeave => 'warning',
            self::Terminated => 'danger',
        };
    }

    public function canLogin(): bool
    {
        return in_array($this, [self::Active, self::OnLeave]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
