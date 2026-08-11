<?php

namespace App\Enum;

enum FeedbackType: string
{
    case BUG = 'bug';
    case IDEA = 'idea';
    case SUPPORT = 'support';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::BUG => 'Bug',
            self::IDEA => 'Idée',
            self::SUPPORT => 'Support',
            self::OTHER => 'Autre',
        };
    }

    public function defaultSubject(): string
    {
        return match ($this) {
            self::BUG => 'Problème rencontré',
            self::IDEA => 'Suggestion d’amélioration',
            self::SUPPORT => 'Demande d’aide',
            self::OTHER => 'Autre demande',
        };
    }
}
