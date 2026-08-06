<?php

namespace App\Entity;

use App\Enum\CalendarSlotStatus;
use App\Enum\DayPeriod;
use App\Repository\CalendarSlotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CalendarSlotRepository::class)]
#[ORM\UniqueConstraint(
    name: 'uniq_calendar_slot_campaign_date_period',
    columns: ['campaign_id', 'date', 'period'],
)]
class CalendarSlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 32, enumType: DayPeriod::class)]
    private DayPeriod $period;

    #[ORM\Column(length: 32, enumType: CalendarSlotStatus::class)]
    private CalendarSlotStatus $status = CalendarSlotStatus::OPEN;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $blockedReason = null;

    #[ORM\ManyToOne(inversedBy: 'calendarSlots')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campaign $campaign = null;

    /**
     * @var Collection<int, Availability>
     */
    #[ORM\OneToMany(targetEntity: Availability::class, mappedBy: 'calendarSlot')]
    private Collection $availabilities;

    public function __construct()
    {
        $this->availabilities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getPeriod(): DayPeriod
    {
        return $this->period;
    }

    public function setPeriod(DayPeriod $period): static
    {
        $this->period = $period;

        return $this;
    }

    public function getStatus(): CalendarSlotStatus
    {
        return $this->status;
    }

    public function getBlockedReason(): ?string
    {
        return $this->blockedReason;
    }

    public function isOpen(): bool
    {
        return CalendarSlotStatus::OPEN === $this->status;
    }

    public function isBlocked(): bool
    {
        return CalendarSlotStatus::BLOCKED === $this->status;
    }

    public function isSelected(): bool
    {
        return CalendarSlotStatus::SELECTED === $this->status;
    }

    public function block(?string $reason = null): void
    {
        $reason = null !== $reason ? trim($reason) : null;

        $this->status = CalendarSlotStatus::BLOCKED;
        $this->blockedReason = '' !== $reason ? $reason : null;
    }

    public function reopen(): void
    {
        $this->status = CalendarSlotStatus::OPEN;
        $this->blockedReason = null;
    }

    public function select(): void
    {
        $this->status = CalendarSlotStatus::SELECTED;
        $this->blockedReason = null;
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(Campaign $campaign): static
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * @return Collection<int, Availability>
     */
    public function getAvailabilities(): Collection
    {
        return $this->availabilities;
    }

    public function addAvailability(Availability $availability): static
    {
        if (!$this->availabilities->contains($availability)) {
            $this->availabilities->add($availability);
            $availability->setCalendarSlot($this);
        }

        return $this;
    }
    
}
