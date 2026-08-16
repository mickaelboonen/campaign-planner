<?php

namespace App\Enum;

enum FeedbackStatus: string
{
    case NEW = 'new';
    case READ = 'read';
    case CLOSED = 'closed';

    public function translationKey(): string
    {
        return match ($this) {
            self::NEW => 'feedback_status.new',
            self::READ => 'feedback_status.read',
            self::CLOSED => 'feedback_status.closed',
        };
    }
}
