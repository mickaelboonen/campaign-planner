<?php

namespace App\Enum;

enum SubscriptionPlan: string
{
    case FREE = 'free';
    case PREMIUM = 'premium';

    public function label(): string
    {
        return match ($this) {
            self::FREE => 'Gratuit',
            self::PREMIUM => 'Premium',
        };
    }
}
