<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Received = 'received';
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Received => 'Received',
            self::Scheduled => 'Technician scheduled',
            self::InProgress => 'In progress',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Received => 'attention',
            self::Resolved => 'good',
            self::Closed => 'muted',
            default => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Received => 'dot',
            self::Scheduled => 'calendar',
            self::InProgress => 'wrench',
            self::Resolved => 'check',
            self::Closed => 'lock',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Received, self::Scheduled, self::InProgress], true);
    }
}
