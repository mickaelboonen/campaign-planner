<?php

namespace App\Enum;

enum CalendarSlotStatus: string
{
    case OPEN = 'open';
    case BLOCKED = 'blocked';
    case SELECTED = 'selected';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Ouvert',
            self::BLOCKED => 'Bloqué',
            self::SELECTED => 'Sélectionné',
        };
    }
}