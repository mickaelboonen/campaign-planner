<?php

namespace App\Enum;

enum FeedbackType: string
{
    case BUG = 'bug';
    case IDEA = 'idea';
    case SUPPORT = 'support';
    case OTHER = 'other';

    public function translationKey(): string
    {
        return match ($this) {
            self::BUG => 'feedback_type.bug',
            self::IDEA => 'feedback_type.idea',
            self::SUPPORT => 'feedback_type.support',
            self::OTHER => 'feedback_type.other',
        };
    }

    public function defaultSubjectTranslationKey(): string
    {
        return match ($this) {
            self::BUG => 'feedback_type.default_subject.bug',
            self::IDEA => 'feedback_type.default_subject.idea',
            self::SUPPORT => 'feedback_type.default_subject.support',
            self::OTHER => 'feedback_type.default_subject.other',
        };
    }
}
