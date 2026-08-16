<?php

namespace App\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateCampaignData
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\Regex(
        pattern: '/^#[0-9A-Fa-f]{6}$/',
        message: 'campaign.color.invalid_format',
    )]
    public ?string $color = null;

    #[Assert\File(
        maxSize: '5M',
        mimeTypes: [
            'image/jpeg',
            'image/png',
            'image/webp',
        ],
        mimeTypesMessage: 'campaign.image.invalid_type',
    )]
    public ?UploadedFile $imageFile = null;
}
