<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateCampaignData
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\Regex(
        pattern: '/^#[0-9A-Fa-f]{6}$/',
        message: 'La couleur doit être au format hexadécimal.',
    )]
    public ?string $color = null;
}