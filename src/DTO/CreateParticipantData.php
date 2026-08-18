<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateParticipantData
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public ?string $email = null;

    #[Assert\Length(max: 32)]
    #[Assert\Regex(
        pattern: '/^\d{10}$/',
        message: 'participant.phone.invalid_format',
    )]
    public ?string $phone = null;

    #[Assert\Length(max: 255)]
    public ?string $characterName = null;
}
