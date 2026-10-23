<?php

namespace App\Enums;

/** Guyana's ten administrative regions, where an applicant lives. */
enum Region: string
{
    case R1 = 'r1';
    case R2 = 'r2';
    case R3 = 'r3';
    case R4 = 'r4';
    case R5 = 'r5';
    case R6 = 'r6';
    case R7 = 'r7';
    case R8 = 'r8';
    case R9 = 'r9';
    case R10 = 'r10';

    public function label(): string
    {
        return match ($this) {
            self::R1 => 'Region 1 · Barima-Waini',
            self::R2 => 'Region 2 · Pomeroon-Supenaam',
            self::R3 => 'Region 3 · Essequibo Islands-West Demerara',
            self::R4 => 'Region 4 · Demerara-Mahaica (incl. Georgetown)',
            self::R5 => 'Region 5 · Mahaica-Berbice',
            self::R6 => 'Region 6 · East Berbice-Corentyne',
            self::R7 => 'Region 7 · Cuyuni-Mazaruni',
            self::R8 => 'Region 8 · Potaro-Siparuni',
            self::R9 => 'Region 9 · Upper Takutu-Upper Essequibo',
            self::R10 => 'Region 10 · Upper Demerara-Berbice',
        };
    }

    public function short(): string
    {
        return 'Region '.substr($this->value, 1);
    }
}
