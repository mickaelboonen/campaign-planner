<?php

namespace App\Enum;

enum GameSessionStatus: string
{
    case SCHEDULED = 'scheduled';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Prévue',
            self::CANCELLED => 'Annulée',
            self::COMPLETED => 'Terminée',
        };
    }
}
