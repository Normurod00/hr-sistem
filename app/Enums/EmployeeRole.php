<?php

namespace App\Enums;

enum EmployeeRole: string
{
    case Employee = 'employee';
    case Manager = 'manager';
    case Hr = 'hr';
    case SysAdmin = 'sysadmin';

    public function label(): string
    {
        return match ($this) {
            self::Employee => 'Сотрудник',
            self::Manager => 'Руководитель',
            self::Hr => 'HR специалист',
            self::SysAdmin => 'Системный администратор',
        };
    }

    public function canViewAllEmployees(): bool
    {
        return in_array($this, [self::Hr, self::SysAdmin]);
    }

    public function canViewTeam(): bool
    {
        return in_array($this, [self::Manager, self::Hr, self::SysAdmin]);
    }

    public function canManageIntegrations(): bool
    {
        return $this === self::SysAdmin;
    }

    public function canManagePolicies(): bool
    {
        return in_array($this, [self::Hr, self::SysAdmin]);
    }

    public function canViewAuditLogs(): bool
    {
        return $this === self::SysAdmin;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
