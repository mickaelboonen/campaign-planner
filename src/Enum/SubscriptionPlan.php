<?php

namespace App\Enum;

enum SubscriptionPlan: string
{
    case FREE = 'free';
    case PREMIUM = 'premium';

    public function translationKey(): string
    {
        return match ($this) {
            self::FREE => 'subscription_plan.free',
            self::PREMIUM => 'subscription_plan.premium',
        };
    }
}
