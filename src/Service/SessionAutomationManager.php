<?php

namespace App\Service;

use App\Repository\GameSessionRepository;
use App\Service\Notification\SessionNotificationManager;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SessionAutomationManager
{
    public function __construct(
        private GameSessionRepository $gameSessionRepository,
        private SessionNotificationManager $sessionNotificationManager,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(): void
    {
        $today = new \DateTimeImmutable('today');

        $this->completePastSessions($today);
        $this->sendReminders($today);
    }

    private function completePastSessions(\DateTimeImmutable $today): void
    {
        $sessions = $this->gameSessionRepository
            ->findPastScheduledSessions($today);

        foreach ($sessions as $session) {
            $session->complete();
        }

        $this->entityManager->flush();
    }

    private function sendReminders(\DateTimeImmutable $today): void
    {
        $reminderDate = $today->modify('+7 days');

        $sessions = $this->gameSessionRepository
            ->findSessionsNeedingReminder($reminderDate);

        foreach ($sessions as $session) {
            $this->sessionNotificationManager
                ->notifySessionReminder($session);

            $session->markReminderSent();
        }

        $this->entityManager->flush();
    }
}
