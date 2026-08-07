<?php

namespace App\Service\Notification;

use App\Entity\GameSession;

final readonly class SessionNotificationManager
{
    /**
     * @param iterable<SessionNotifierInterface> $notifiers
     */
    public function __construct(
        private iterable $notifiers,
    ) {
    }

    public function notifySessionScheduled(
        GameSession $session,
    ): void {
        foreach ($this->notifiers as $notifier) {
            $notifier->notify($session);
        }
    }
}
