<?php

namespace App\Enums;

enum ComplianceCategory: string
{
    case Certification = 'certification';
    case Insurance = 'insurance';
    case Hse = 'hse';
    case Registration = 'registration';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Certification => 'Certification',
            self::Insurance => 'Insurance',
            self::Hse => 'HSE policy',
            self::Registration => 'Registration & compliance',
            self::Other => 'Other',
        };
    }
}
