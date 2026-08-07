<?php

namespace App\Service\Notification;

use App\Entity\GameSession;

interface SessionNotifierInterface
{
    public function notify(GameSession $session): void;
}
