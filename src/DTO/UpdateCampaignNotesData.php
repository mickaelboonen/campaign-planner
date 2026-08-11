<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateCampaignNotesData
{
    #[Assert\Length(max: 5000)]
    public ?string $privateNotes = null;
}
