<?php

namespace App\Enums;

enum UserRole: string
{
    case Candidate = 'candidate';
    case Employee = 'employee'; // Обычный сотрудник BRB
    case Hr = 'hr';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Candidate => 'Кандидат',
            self::Employee => 'Сотрудник',
            self::Hr => 'HR-менеджер',
            self::Admin => 'Администратор',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Candidate => 'blue',
            self::Employee => 'teal',
            self::Hr => 'green',
            self::Admin => 'purple',
        };
    }

    /**
     * Проверить, является ли роль сотрудником (не кандидатом)
     */
    public function isEmployee(): bool
    {
        return in_array($this, [self::Employee, self::Hr, self::Admin]);
    }

    /**
     * Проверить, есть ли доступ к админ-панели
     */
    public function hasAdminAccess(): bool
    {
        return in_array($this, [self::Hr, self::Admin]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
