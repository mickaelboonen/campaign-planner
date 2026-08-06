<?php

namespace App\Service;

use App\Entity\Availability;
use App\Entity\CalendarSlot;
use App\Entity\Participant;
use App\Enum\AvailabilityStatus;
use App\Enum\DayPeriod;
use App\ViewModel\Calendar\CalendarCellView;
use App\ViewModel\Calendar\CalendarDayView;
use App\ViewModel\Calendar\CalendarRowView;
use App\ViewModel\Calendar\CalendarSlotSummaryView;
use App\ViewModel\Calendar\CalendarWeekView;

final readonly class CalendarViewBuilder
{
    /**
     * @param list<CalendarSlot> $slots
     * @param list<Participant>  $participants
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
            $date = $weekStart->modify(
                sprintf('+%d days', $dayOffset),
            );

            $dateKey = $date->format('Y-m-d');

            $afternoonSlot = $slotsByDateAndPeriod[
                $dateKey
            ][DayPeriod::AFTERNOON->value];

            $eveningSlot = $slotsByDateAndPeriod[
                $dateKey
            ][DayPeriod::EVENING->value];

            $days[] = new CalendarDayView(
                date: $date,
                afternoonSlot: $afternoonSlot,
                eveningSlot: $eveningSlot,
                afternoonSummary: $this->buildSlotSummary(
                    $afternoonSlot,
                    $participants,
                    $availabilitiesByParticipantAndSlot,
                ),
                eveningSummary: $this->buildSlotSummary(
                    $eveningSlot,
                    $participants,
                    $availabilitiesByParticipantAndSlot,
                ),
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
                    $participantId = $participant->getId();
                    $slotId = $slot->getId();

                    $availability = null;

                    if (
                        $participantId !== null
                        && $slotId !== null
                    ) {
                        $availability =
                            $availabilitiesByParticipantAndSlot[
                                $participantId
                            ][$slotId] ?? null;
                    }

                    $cells[] = new CalendarCellView(
                        slot: $slot,
                        availability: $availability,
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
            $date = $slot->getDate();

            if ($date === null) {
                continue;
            }

            $indexed[
                $date->format('Y-m-d')
            ][
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
    private function indexAvailabilities(
        array $availabilities,
    ): array {
        $indexed = [];

        foreach ($availabilities as $availability) {
            $participantId = $availability
                ->getParticipant()
                ?->getId();

            $slotId = $availability
                ->getCalendarSlot()
                ?->getId();

            if (
                $participantId === null
                || $slotId === null
            ) {
                continue;
            }

            $indexed[$participantId][$slotId] = $availability;
        }

        return $indexed;
    }

    /**
     * @param list<Participant> $participants
     * @param array<int, array<int, Availability>>
     *     $availabilitiesByParticipantAndSlot
     */
    private function buildSlotSummary(
        CalendarSlot $slot,
        array $participants,
        array $availabilitiesByParticipantAndSlot,
    ): CalendarSlotSummaryView {
        $availableCount = 0;
        $maybeCount = 0;
        $unavailableCount = 0;
        $unansweredCount = 0;

        $slotId = $slot->getId();

        if ($slotId === null) {
            return new CalendarSlotSummaryView(
                availableCount: 0,
                maybeCount: 0,
                unavailableCount: 0,
                unansweredCount: count($participants),
            );
        }

        foreach ($participants as $participant) {
            $participantId = $participant->getId();

            if ($participantId === null) {
                continue;
            }

            $availability =
                $availabilitiesByParticipantAndSlot[
                    $participantId
                ][$slotId] ?? null;

            if ($availability === null) {
                ++$unansweredCount;

                continue;
            }

            match ($availability->getStatus()) {
                AvailabilityStatus::AVAILABLE => ++$availableCount,
                AvailabilityStatus::MAYBE => ++$maybeCount,
                AvailabilityStatus::UNAVAILABLE => ++$unavailableCount,
            };
        }

        return new CalendarSlotSummaryView(
            availableCount: $availableCount,
            maybeCount: $maybeCount,
            unavailableCount: $unavailableCount,
            unansweredCount: $unansweredCount,
        );
    }
}