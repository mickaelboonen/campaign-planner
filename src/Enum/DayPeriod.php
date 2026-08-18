<?php

namespace App\Enum;

enum DayPeriod: string
{
    case AFTERNOON = 'afternoon';
    case EVENING = 'evening';

    public function translationKey(): string
    {
        return match ($this) {
            self::AFTERNOON => 'day_period.afternoon',
            self::EVENING => 'day_period.evening',
        };
    }
}
