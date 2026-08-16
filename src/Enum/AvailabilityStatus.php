<?php

namespace App\Enum;

enum AvailabilityStatus: string
{
    case AVAILABLE = 'available';
    case MAYBE = 'maybe';
    case UNAVAILABLE = 'unavailable';

    public function translationKey(): string
    {
        return match ($this) {
            self::AVAILABLE => 'availability_status.available',
            self::MAYBE => 'availability_status.maybe',
            self::UNAVAILABLE => 'availability_status.unavailable',
        };
    }

    public function cssClass(): string
    {
        return match ($this) {
            self::AVAILABLE => 'is-available',
            self::MAYBE => 'is-maybe',
            self::UNAVAILABLE => 'is-unavailable',
        };
    }
}
