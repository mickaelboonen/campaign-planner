<?php

namespace App\Enum;

enum GameSessionStatus: string
{
    case SCHEDULED = 'scheduled';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';

    public function translationKey(): string
    {
        return match ($this) {
            self::SCHEDULED => 'game_session_status.scheduled',
            self::CANCELLED => 'game_session_status.cancelled',
            self::COMPLETED => 'game_session_status.completed',
        };
    }
}
