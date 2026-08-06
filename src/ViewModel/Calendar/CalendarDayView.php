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
    ) {
    }

    public function getLabel(): string
    {
        return match ($this->date->format('N')) {
            '1' => 'Lundi',
            '2' => 'Mardi',
            '3' => 'Mercredi',
            '4' => 'Jeudi',
            '5' => 'Vendredi',
            '6' => 'Samedi',
            '7' => 'Dimanche',
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