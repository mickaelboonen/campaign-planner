<?php

namespace App\ViewModel\Calendar;

use App\Entity\Availability;
use App\Entity\CalendarSlot;
use App\Enum\AvailabilityStatus;

final readonly class CalendarCellView
{
    public function __construct(
        public CalendarSlot $slot,
        public ?Availability $availability,
    ) {
    }

    public function isBlocked(): bool
    {
        return $this->slot->isBlocked();
    }

    public function getStatus(): ?AvailabilityStatus
    {
        return $this->availability?->getStatus();
    }

    public function getCssClass(): string
    {
        if ($this->isBlocked()) {
            return 'is-blocked';
        }

        return match ($this->getStatus()) {
            AvailabilityStatus::AVAILABLE => 'is-available',
            AvailabilityStatus::MAYBE => 'is-maybe',
            AvailabilityStatus::UNAVAILABLE => 'is-unavailable',
            null => 'is-empty',
        };
    }

    public function getLabel(): string
    {
        if ($this->isBlocked()) {
            return 'Bloqué';
        }

        return $this->getStatus()?->label() ?? 'Non renseigné';
    }

    public function getSymbol(): string
    {
        if ($this->isBlocked()) {
            return '×';
        }

        return match ($this->getStatus()) {
            AvailabilityStatus::AVAILABLE => '✓',
            AvailabilityStatus::MAYBE => '?',
            AvailabilityStatus::UNAVAILABLE => '×',
            null => '—',
        };
    }
}