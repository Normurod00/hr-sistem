<?php

namespace App\Enums;

enum IntegrationType: string
{
    case Kpi = 'kpi';
    case Pulse = 'pulse';
    case SmartOffice = 'smart_office';
    case Iabs = 'iabs';
    case AiServer = 'ai_server';

    public function label(): string
    {
        return match ($this) {
            self::Kpi => 'KPI System',
            self::Pulse => 'Pulse',
            self::SmartOffice => 'Smart Office',
            self::Iabs => 'iABS',
            self::AiServer => 'AI Server',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Kpi => 'Система управления показателями эффективности',
            self::Pulse => 'Система опросов и обратной связи',
            self::SmartOffice => 'Управление офисной инфраструктурой',
            self::Iabs => 'Автоматизированная банковская система',
            self::AiServer => 'Сервер искусственного интеллекта',
        };
    }

    public function configKey(): string
    {
        return match ($this) {
            self::Kpi => 'integrations.kpi',
            self::Pulse => 'integrations.pulse',
            self::SmartOffice => 'integrations.smart_office',
            self::Iabs => 'integrations.iabs',
            self::AiServer => 'ai',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
