<?php

namespace App\Enum;

enum FeedbackStatus: string
{
    case NEW = 'new';
    case READ = 'read';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Nouveau',
            self::READ => 'Lu',
            self::CLOSED => 'Clôturé',
        };
    }
}
