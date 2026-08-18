<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class GameSessionData
{
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\NotNull(
        message: 'game_session.start_time.required',
    )]
    public ?\DateTimeImmutable $startTime = null;

    #[Assert\NotNull(
        message: 'game_session.end_time.required',
    )]
    public ?\DateTimeImmutable $endTime = null;
}
