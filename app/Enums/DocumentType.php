<?php

namespace App\Enums;

/** Documents an applicant uploads with an application. */
enum DocumentType: string
{
    case PoliceClearance = 'police_clearance';
    case Identification = 'identification';
    case Certificate = 'certificate';
    case Cv = 'cv';

    public function label(): string
    {
        return match ($this) {
            self::PoliceClearance => 'Police clearance',
            self::Identification => 'National ID or passport',
            self::Certificate => 'Certificates',
            self::Cv => 'CV',
        };
    }
}
