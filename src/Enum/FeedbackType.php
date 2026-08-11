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
}
