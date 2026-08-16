<?php

namespace App\Enum;

enum CalendarSlotStatus: string
{
    case OPEN = 'open';
    case BLOCKED = 'blocked';
    case SELECTED = 'selected';

    public function translationKey(): string
    {
        return match ($this) {
            self::OPEN => 'calendar_slot_status.open',
            self::BLOCKED => 'calendar_slot_status.blocked',
            self::SELECTED => 'calendar_slot_status.selected',
        };
    }
}
