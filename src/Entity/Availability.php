<?php

namespace App\Entity;

use App\Enum\AvailabilityStatus;
use App\Repository\AvailabilityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvailabilityRepository::class)]
#[ORM\UniqueConstraint(
    name: 'uniq_availability_participant_slot',
    columns: ['participant_id', 'calendar_slot_id'],
)]
class Availability
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32, enumType: AvailabilityStatus::class)]
    private AvailabilityStatus $status;

    #[ORM\ManyToOne(inversedBy: 'availabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $participant = null;

    #[ORM\ManyToOne(inversedBy: 'availabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CalendarSlot $calendarSlot = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): AvailabilityStatus
    {
        return $this->status;
    }

    public function setStatus(AvailabilityStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(Participant $participant): static
    {
        $this->participant = $participant;

        return $this;
    }

    public function getCalendarSlot(): ?CalendarSlot
    {
        return $this->calendarSlot;
    }

    public function setCalendarSlot(CalendarSlot $calendarSlot): static
    {
        $this->calendarSlot = $calendarSlot;

        return $this;
    }
}