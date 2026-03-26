<?php

namespace App\Enums;

enum EmploymentType: string
{
    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Contract = 'contract';
    case Remote = 'remote';
    case Internship = 'internship';
    case Freelance = 'freelance';

    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'Полная занятость',
            self::PartTime => 'Частичная занятость',
            self::Contract => 'Контракт',
            self::Remote => 'Удалённая работа',
            self::Internship => 'Стажировка',
            self::Freelance => 'Фриланс',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::FullTime => 'green',
            self::PartTime => 'blue',
            self::Contract => 'yellow',
            self::Remote => 'purple',
            self::Internship => 'cyan',
            self::Freelance => 'orange',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
