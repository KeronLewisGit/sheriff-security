<?php

namespace App\Enums;

enum TicketIssue: string
{
    case FalseAlarms = 'false_alarms';
    case Fault = 'fault';
    case NoSignal = 'no_signal';
    case Keypad = 'keypad';
    case Battery = 'battery';
    case NewInstall = 'new_install';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FalseAlarms => 'Repeated false alarms',
            self::Fault => 'Panel shows a fault or trouble light',
            self::NoSignal => 'Command centre not receiving signals',
            self::Keypad => 'Keypad, code or arming problem',
            self::Battery => 'Low battery or power warning',
            self::NewInstall => 'New installation or extra sensors',
            self::Other => 'Something else',
        };
    }
}
