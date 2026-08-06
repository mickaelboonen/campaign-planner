<?php

namespace App\ViewModel\Calendar;

final readonly class CalendarWeekView
{
    /**
     * @param list<CalendarDayView> $days
     * @param list<CalendarRowView> $rows
     */
    public function __construct(
        public \DateTimeImmutable $start,
        public \DateTimeImmutable $end,
        public array $days,
        public array $rows,
    ) {
    }

    public function getPreviousWeek(): \DateTimeImmutable
    {
        return $this->start->modify('-7 days');
    }

    public function getNextWeek(): \DateTimeImmutable
    {
        return $this->start->modify('+7 days');
    }
}