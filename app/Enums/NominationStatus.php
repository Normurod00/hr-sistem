<?php

namespace App\Enums;

enum NominationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Кўриб чиқилмоқда',
            self::Approved => 'Тасдиқланган',
            self::Rejected => 'Рад этилган',
        };
    }

    public function labelRu(): string
    {
        return match ($this) {
            self::Pending => 'На рассмотрении',
            self::Approved => 'Одобрено',
            self::Rejected => 'Отклонено',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'bi-hourglass-split',
            self::Approved => 'bi-check-circle-fill',
            self::Rejected => 'bi-x-circle-fill',
        };
    }
}
