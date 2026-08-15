<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class GameSessionData
{

    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\NotNull(message: 'L’heure de début est obligatoire.')]
    public ?\DateTimeImmutable $startTime = null;

    #[Assert\NotNull(message: 'L’heure de fin est obligatoire.')]
    public ?\DateTimeImmutable $endTime = null;
}
