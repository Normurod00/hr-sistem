<?php

namespace App\Enums;

enum MessageRole: string
{
    case User = 'user';
    case Assistant = 'assistant';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Пользователь',
            self::Assistant => 'AI Ассистент',
            self::System => 'Система',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
