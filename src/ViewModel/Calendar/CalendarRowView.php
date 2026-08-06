<?php

namespace App\ViewModel\Calendar;

use App\Entity\Participant;

final readonly class CalendarRowView
{
    /**
     * @param list<CalendarCellView> $cells
     */
    public function __construct(
        public Participant $participant,
        public array $cells,
    ) {
    }
}