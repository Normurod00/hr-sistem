<?php

namespace App\Enums;

enum FileType: string
{
    case Resume = 'resume';
    case IdDocument = 'id_document';
    case Certificate = 'certificate';
    case Portfolio = 'portfolio';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Resume => 'Резюме',
            self::IdDocument => 'Документ',
            self::Certificate => 'Сертификат',
            self::Portfolio => 'Портфолио',
            self::Other => 'Другое',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Resume => 'document-text',
            self::IdDocument => 'identification',
            self::Certificate => 'academic-cap',
            self::Portfolio => 'briefcase',
            self::Other => 'paper-clip',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
