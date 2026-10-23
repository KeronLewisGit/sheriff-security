<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case New = 'new';
    case Screening = 'screening';
    case Interview = 'interview';
    case Training = 'training';
    case Hired = 'hired';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Screening => 'Screening',
            self::Interview => 'Interview',
            self::Training => 'Training',
            self::Hired => 'Hired',
            self::Rejected => 'Rejected',
        };
    }

    /** Colour is reserved for state that needs attention or is final; the rest stay neutral. */
    public function tone(): string
    {
        return match ($this) {
            self::New => 'attention',
            self::Hired => 'good',
            self::Rejected => 'muted',
            default => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'dot',
            self::Screening => 'search',
            self::Interview => 'users',
            self::Training => 'graduation',
            self::Hired => 'check',
            self::Rejected => 'x',
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Hired, self::Rejected], true);
    }
}
