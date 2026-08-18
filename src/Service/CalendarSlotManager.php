<?php

namespace App\Service;

use App\Entity\CalendarSlot;
use App\Entity\Campaign;
use App\Enum\DayPeriod;
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
            $currentDate = $weekStart->modify(
                sprintf('+%d days', $dayOffset),
            );

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

        return $this->calendarSlotRepository
            ->findByCampaignAndWeek($campaign, $weekStart);
    }

    /**
     * @param array<string|int, mixed> $submittedSlots
     */
    public function saveBlockingStates(
        Campaign $campaign,
        array $submittedSlots,
    ): void {
        $normalizedStates = [];

        foreach ($submittedSlots as $slotId => $status) {
            $validatedSlotId = filter_var(
                $slotId,
                FILTER_VALIDATE_INT,
            );

            if ($validatedSlotId === false || $validatedSlotId <= 0) {
                throw new \InvalidArgumentException(
                    'calendar.invalid_slot_id',
                );
            }

            if (!is_string($status)) {
                throw new \InvalidArgumentException(
                    'calendar.invalid_slot_state',
                );
            }

            if (!in_array($status, ['open', 'blocked'], true)) {
                throw new \InvalidArgumentException(
                    'calendar.unknown_slot_state',
                );
            }

            $normalizedStates[$validatedSlotId] = $status;
        }

        $slotIds = array_keys($normalizedStates);

        $slots = $this->calendarSlotRepository
            ->findByIdsAndCampaign($slotIds, $campaign);

        if (count($slots) !== count($slotIds)) {
            throw new \InvalidArgumentException(
                'calendar.invalid_slots',
            );
        }

        foreach ($slots as $slot) {
            $slotId = $slot->getId();

            if ($slotId === null) {
                continue;
            }

            match ($normalizedStates[$slotId]) {
                'blocked' => $slot->block(),
                'open' => $slot->reopen(),
            };
        }

        $this->entityManager->flush();
    }

    private function getWeekStart(
        \DateTimeImmutable $date,
    ): \DateTimeImmutable {
        return $date
            ->modify('monday this week')
            ->setTime(0, 0);
    }
}
