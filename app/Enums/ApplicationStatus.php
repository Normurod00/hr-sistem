<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case New = 'new';
    case InReview = 'in_review';
    case Invited = 'invited';
    case Rejected = 'rejected';
    case Hired = 'hired';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Новая',
            self::InReview => 'На рассмотрении',
            self::Invited => 'Приглашён',
            self::Rejected => 'Отклонён',
            self::Hired => 'Принят',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'blue',
            self::InReview => 'yellow',
            self::Invited => 'purple',
            self::Rejected => 'red',
            self::Hired => 'green',
        };
    }

    public function bgClass(): string
    {
        return match ($this) {
            self::New => 'bg-blue-100 text-blue-700',
            self::InReview => 'bg-yellow-100 text-yellow-700',
            self::Invited => 'bg-purple-100 text-purple-700',
            self::Rejected => 'bg-red-100 text-red-700',
            self::Hired => 'bg-green-100 text-green-700',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
