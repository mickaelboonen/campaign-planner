<?php

namespace App\Service;

use App\Entity\Availability;
use App\Entity\CalendarSlot;
use App\Entity\Participant;
use App\Enum\DayPeriod;
use App\ViewModel\Calendar\CalendarCellView;
use App\ViewModel\Calendar\CalendarDayView;
use App\ViewModel\Calendar\CalendarRowView;
use App\ViewModel\Calendar\CalendarWeekView;

final readonly class CalendarViewBuilder
{
    /**
     * @param list<CalendarSlot> $slots
     * @param list<Participant> $participants
     * @param list<Availability> $availabilities
     */
    public function build(
        \DateTimeImmutable $weekStart,
        array $slots,
        array $participants,
        array $availabilities,
    ): CalendarWeekView {
        $slotsByDateAndPeriod = $this->indexSlots($slots);
        $availabilitiesByParticipantAndSlot = $this->indexAvailabilities(
            $availabilities,
        );

        $days = [];

        foreach (range(0, 6) as $dayOffset) {
            $date = $weekStart->modify(sprintf('+%d days', $dayOffset));
            $dateKey = $date->format('Y-m-d');

            $days[] = new CalendarDayView(
                date: $date,
                afternoonSlot: $slotsByDateAndPeriod[$dateKey][DayPeriod::AFTERNOON->value],
                eveningSlot: $slotsByDateAndPeriod[$dateKey][DayPeriod::EVENING->value],
            );
        }

        $rows = [];

        foreach ($participants as $participant) {
            $cells = [];

            foreach ($days as $day) {
                foreach ([
                    $day->afternoonSlot,
                    $day->eveningSlot,
                ] as $slot) {
                    $cells[] = new CalendarCellView(
                        slot: $slot,
                        availability: $availabilitiesByParticipantAndSlot[
                            $participant->getId()
                        ][$slot->getId()] ?? null,
                    );
                }
            }

            $rows[] = new CalendarRowView(
                participant: $participant,
                cells: $cells,
            );
        }

        return new CalendarWeekView(
            start: $weekStart,
            end: $weekStart->modify('+6 days'),
            days: $days,
            rows: $rows,
        );
    }

    /**
     * @param list<CalendarSlot> $slots
     *
     * @return array<string, array<string, CalendarSlot>>
     */
    private function indexSlots(array $slots): array
    {
        $indexed = [];

        foreach ($slots as $slot) {
            $indexed[$slot->getDate()->format('Y-m-d')][
                $slot->getPeriod()->value
            ] = $slot;
        }

        return $indexed;
    }

    /**
     * @param list<Availability> $availabilities
     *
     * @return array<int, array<int, Availability>>
     */
    private function indexAvailabilities(array $availabilities): array
    {
        $indexed = [];

        foreach ($availabilities as $availability) {
            $participantId = $availability->getParticipant()->getId();
            $slotId = $availability->getCalendarSlot()->getId();

            $indexed[$participantId][$slotId] = $availability;
        }

        return $indexed;
    }
}