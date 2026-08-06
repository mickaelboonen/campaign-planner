<?php

namespace App\Service;

use App\Entity\CalendarSlot;
use App\Entity\Campaign;
use App\Enum\DayPeriod;
use App\ViewModel\Calendar\CalendarDayView;
use App\ViewModel\Calendar\CalendarWeekView;
use App\Repository\CalendarSlotRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CalendarSlotManager
{
    public function __construct(
        private CalendarSlotRepository $calendarSlotRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return list<CalendarSlot>
     */
    public function getOrCreateWeek(
        Campaign $campaign,
        \DateTimeImmutable $date,
    ): array {
        $weekStart = $this->getWeekStart($date);

        foreach (range(0, 6) as $dayOffset) {
            $currentDate = $weekStart->modify(sprintf('+%d days', $dayOffset));

            foreach (DayPeriod::cases() as $period) {
                $existingSlot = $this->calendarSlotRepository
                    ->findOneByCampaignDateAndPeriod(
                        $campaign,
                        $currentDate,
                        $period,
                    );

                if ($existingSlot !== null) {
                    continue;
                }

                $slot = new CalendarSlot();
                $slot->setCampaign($campaign);
                $slot->setDate($currentDate);
                $slot->setPeriod($period);

                $this->entityManager->persist($slot);
            }
        }

        $this->entityManager->flush();

        return $this->calendarSlotRepository->findByCampaignAndWeek(
            $campaign,
            $weekStart,
        );
    }

    private function getWeekStart(
        \DateTimeImmutable $date,
    ): \DateTimeImmutable {
        return $date->modify('monday this week')->setTime(0, 0);
    }
}