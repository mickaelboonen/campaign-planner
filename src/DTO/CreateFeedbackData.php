<?php

namespace App\DTO;

use App\Enum\FeedbackType;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateFeedbackData
{
    #[Assert\NotNull]
    public ?FeedbackType $type = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 5000)]
    public ?string $message = null;

    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 120,
    )]
    public ?string $subject = null;
}
