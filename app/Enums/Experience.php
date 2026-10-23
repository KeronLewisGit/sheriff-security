<?php

namespace App\Enums;

enum Experience: string
{
    case None = 'none';
    case UnderTwo = 'under_2';
    case TwoToFive = '2_5';
    case OverFive = 'over_5';
    case Forces = 'forces';

    public function label(): string
    {
        return match ($this) {
            self::None => 'New to security',
            self::UnderTwo => 'Under 2 years in security',
            self::TwoToFive => '2 to 5 years in security',
            self::OverFive => 'More than 5 years in security',
            self::Forces => 'Former army, police or coast guard',
        };
    }
}
