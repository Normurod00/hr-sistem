<?php

namespace App\Enums;

enum IntegrationStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Error = 'error';
    case Timeout = 'timeout';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'В процессе',
            self::Success => 'Успешно',
            self::Error => 'Ошибка',
            self::Timeout => 'Таймаут',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Success => 'success',
            self::Error => 'danger',
            self::Timeout => 'secondary',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
