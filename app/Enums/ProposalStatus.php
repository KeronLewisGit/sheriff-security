<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case New = 'new';
    case Qualified = 'qualified';
    case SiteSurvey = 'site_survey';
    case ProposalSent = 'proposal_sent';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Qualified => 'Qualified',
            self::SiteSurvey => 'Site survey',
            self::ProposalSent => 'Proposal sent',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::New => 'attention',
            self::Won => 'good',
            self::Lost => 'muted',
            default => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'dot',
            self::Qualified => 'check-circle',
            self::SiteSurvey => 'map-pin',
            self::ProposalSent => 'mail',
            self::Won => 'check',
            self::Lost => 'x',
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Won, self::Lost], true);
    }
}
