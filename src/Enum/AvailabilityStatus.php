<?php

namespace App\Enum;

enum AvailabilityStatus: string
{
    case AVAILABLE = 'available';
    case MAYBE = 'maybe';
    case UNAVAILABLE = 'unavailable';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Disponible',
            self::MAYBE => 'Peut-être',
            self::UNAVAILABLE => 'Indisponible',
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