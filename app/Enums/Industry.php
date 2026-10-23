<?php

namespace App\Enums;

enum Industry: string
{
    case OilGas = 'oil_gas';
    case Maritime = 'maritime';
    case Financial = 'financial';
    case Commercial = 'commercial';
    case Diplomatic = 'diplomatic';
    case Government = 'government';
    case Residential = 'residential';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::OilGas => 'Oil, gas & energy',
            self::Maritime => 'Maritime, port & logistics',
            self::Financial => 'Banking & cash handling',
            self::Commercial => 'Commercial, retail & hospitality',
            self::Diplomatic => 'Diplomatic & corporate',
            self::Government => 'Government & public sector',
            self::Residential => 'Residential',
            self::Other => 'Something else',
        };
    }
}
