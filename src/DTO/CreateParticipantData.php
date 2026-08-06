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
    public ?string $phone = null;

    #[Assert\Length(max: 255)]
    public ?string $characterName = null;
}