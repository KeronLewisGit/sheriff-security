<?php

namespace App\Enums;

enum SiteType: string
{
    case Onshore = 'onshore';
    case Shorebase = 'shorebase';
    case Offshore = 'offshore';
    case Port = 'port';
    case Urban = 'urban';
    case Multiple = 'multiple';

    public function label(): string
    {
        return match ($this) {
            self::Onshore => 'Onshore facility or yard',
            self::Shorebase => 'Shorebase',
            self::Offshore => 'Offshore support (vessel / installation)',
            self::Port => 'Port, wharf or terminal',
            self::Urban => 'Office, retail or residence',
            self::Multiple => 'Several sites',
        };
    }

    public function short(): string
    {
        return match ($this) {
            self::Onshore => 'Onshore',
            self::Shorebase => 'Shorebase',
            self::Offshore => 'Offshore',
            self::Port => 'Port',
            self::Urban => 'Urban',
            self::Multiple => 'Multi-site',
        };
    }
}
