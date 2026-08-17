<?php

namespace App\ViewModel\Calendar;

use App\Entity\CalendarSlot;
use App\Enum\DayPeriod;

final readonly class CalendarDayView
{
    public function __construct(
        public \DateTimeImmutable $date,
        public CalendarSlot $afternoonSlot,
        public CalendarSlot $eveningSlot,
        public CalendarSlotSummaryView $afternoonSummary,
        public CalendarSlotSummaryView $eveningSummary,
    ) {
    }

    public function getTranslationKey(): string
    {
        return match ($this->date->format('N')) {
            '1' => 'days.monday',
            '2' => 'days.tuesday',
            '3' => 'days.wednesday',
            '4' => 'days.thursday',
            '5' => 'days.friday',
            '6' => 'days.saturday',
            '7' => 'days.sunday',
        };
    }

    public function getSlot(DayPeriod $period): CalendarSlot
    {
        return match ($period) {
            DayPeriod::AFTERNOON => $this->afternoonSlot,
            DayPeriod::EVENING => $this->eveningSlot,
        };
    }
}
