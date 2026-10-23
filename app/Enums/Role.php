<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case BusinessDevelopment = 'bd';
    case HumanResources = 'hr';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::BusinessDevelopment => 'Business development',
            self::HumanResources => 'Human resources',
        };
    }

    /**
     * Areas of the dashboard each role works in. Admin sees everything.
     * "sales": proposals, vendor packs, compliance documents, alarm tickets, messages.
     * "hiring": jobs and applications. "admin": users and settings.
     */
    public function can(string $area): bool
    {
        return match ($this) {
            self::Admin => true,
            self::BusinessDevelopment => $area === 'sales',
            self::HumanResources => $area === 'hiring',
        };
    }
}
