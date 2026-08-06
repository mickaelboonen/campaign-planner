<?php

namespace App\Enum;

enum DayPeriod: string
{
    case AFTERNOON = 'afternoon';
    case EVENING = 'evening';

    public function label(): string
    {
        return match ($this) {
            self::AFTERNOON => 'Après-midi',
            self::EVENING => 'Soir',
        };
    }
}