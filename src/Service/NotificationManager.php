<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;

final readonly class NotificationManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function notifyAvailabilityUpdated(
        Participant $participant,
        \DateTimeImmutable $weekStart,
        int $changedCount,
    ): void {
        if ($changedCount <= 0) {
            return;
        }

        $campaign = $participant->getCampaign();

        $notification = new Notification();
        $notification->setGm($campaign->getOwner());
        $notification->setCampaign($campaign);
        $notification->setParticipant($participant);
        $notification->setType(
            Notification::TYPE_AVAILABILITY_UPDATED,
        );
        $notification->setCount($changedCount);
        $notification->setWeekStart($weekStart);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
