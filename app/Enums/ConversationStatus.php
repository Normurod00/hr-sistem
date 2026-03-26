<?php

namespace App\Enums;

enum ConversationStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Активный',
            self::Closed => 'Закрыт',
            self::Archived => 'В архиве',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
