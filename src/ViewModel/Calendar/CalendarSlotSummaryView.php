<?php

namespace App\ViewModel\Calendar;

final readonly class CalendarSlotSummaryView
{
    public function __construct(
        public int $availableCount,
        public int $maybeCount,
        public int $unavailableCount,
        public int $unansweredCount,
    ) {
    }
}