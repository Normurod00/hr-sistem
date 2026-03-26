<?php

namespace App\Enums;

enum AwardType: string
{
    case EmployeeOfMonth = 'employee_of_month';
    case EmployeeOfQuarter = 'employee_of_quarter';
    case EmployeeOfYear = 'employee_of_year';

    public function label(): string
    {
        return match ($this) {
            self::EmployeeOfMonth => 'Ой ходими',
            self::EmployeeOfQuarter => 'Квартал ходими',
            self::EmployeeOfYear => 'Йил ходими',
        };
    }

    public function labelRu(): string
    {
        return match ($this) {
            self::EmployeeOfMonth => 'Сотрудник месяца',
            self::EmployeeOfQuarter => 'Сотрудник квартала',
            self::EmployeeOfYear => 'Сотрудник года',
        };
    }

    public function periodType(): string
    {
        return match ($this) {
            self::EmployeeOfMonth => 'month',
            self::EmployeeOfQuarter => 'quarter',
            self::EmployeeOfYear => 'year',
        };
    }

    public function pointsReward(): int
    {
        return match ($this) {
            self::EmployeeOfMonth => 200,
            self::EmployeeOfQuarter => 500,
            self::EmployeeOfYear => 2000,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::EmployeeOfMonth => 'bi-award-fill',
            self::EmployeeOfQuarter => 'bi-trophy-fill',
            self::EmployeeOfYear => 'bi-gem',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EmployeeOfMonth => '#CD7F32', // Bronze
            self::EmployeeOfQuarter => '#C0C0C0', // Silver
            self::EmployeeOfYear => '#FFD700', // Gold
        };
    }
}
