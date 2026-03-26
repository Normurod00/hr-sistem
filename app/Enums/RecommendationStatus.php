<?php

namespace App\Enums;

enum RecommendationStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает',
            self::InProgress => 'В работе',
            self::Completed => 'Выполнено',
            self::Dismissed => 'Отклонено',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'secondary',
            self::InProgress => 'primary',
            self::Completed => 'success',
            self::Dismissed => 'danger',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
