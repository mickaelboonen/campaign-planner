<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CalendarSlot;
use App\Entity\GameSession;
use App\Enum\CalendarSlotStatus;
use App\Repository\GameSessionRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SessionManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GameSessionRepository $gameSessionRepository,
    ) {
    }

    public function scheduleFromSlot(
        CalendarSlot $slot,
        ?string $name,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime,
    ): GameSession {
        $this->guardSlotCanBeScheduled($slot);

        return $this->entityManager->wrapInTransaction(
            function () use (
                $slot,
                $name,
                $startTime,
                $endTime,
            ): GameSession {
                $session = new GameSession();

                $session
                    ->setCampaign($slot->getCampaign())
                    ->setCalendarSlot($slot)
                    ->setDate($slot->getDate())
                    ->setPeriod($slot->getPeriod())
                    ->setName($name)
                    ->setStartTime($startTime)
                    ->setEndTime($endTime);

                $slot->select();
                $this->entityManager->persist($session);

                return $session;
            },
        );
    }

    public function update(
        GameSession $session,
        ?string $name,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime,
    ): void {
        $session
            ->setName($name)
            ->setStartTime($startTime)
            ->setEndTime($endTime);

        $this->entityManager->flush();
    }

    public function cancel(GameSession $session): void
    {
        if ($session->isCancelled()) {
            throw new \DomainException(
                'session.already_cancelled',
            );
        }

        $session->cancel();

        $slot = $session->getCalendarSlot();

        if ($slot !== null && $slot->isSelected()) {
            $slot->block();
        }

        $this->entityManager->flush();
    }

    private function guardSlotCanBeScheduled(
        CalendarSlot $slot,
    ): void {
        if ($slot->getStatus() !== CalendarSlotStatus::OPEN) {
            throw new \DomainException(
                'session.slot_not_open',
            );
        }

        if ($slot->getDate() < new \DateTimeImmutable('today')) {
            throw new \DomainException(
                'session.slot_in_past',
            );
        }

        $existingSession = $this->gameSessionRepository
            ->findOneBy([
                'calendarSlot' => $slot,
            ]);

        if ($existingSession !== null) {
            throw new \DomainException(
                'session.already_exists',
            );
        }
    }
}
