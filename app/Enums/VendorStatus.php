<?php

namespace App\Enums;

enum VendorStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';
    case Revoked = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting approval',
            self::Approved => 'Approved',
            self::Declined => 'Declined',
            self::Revoked => 'Link revoked',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Pending => 'attention',
            self::Approved => 'good',
            default => 'muted',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'clock',
            self::Approved => 'check',
            self::Declined => 'x',
            self::Revoked => 'lock',
        };
    }
}
