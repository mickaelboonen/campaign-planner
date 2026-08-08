<?php

namespace App\Entity;

use App\Enum\DayPeriod;
use App\Enum\GameSessionStatus;
use App\Repository\GameSessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameSessionRepository::class)]
class GameSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'gameSessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campaign $campaign = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?CalendarSlot $calendarSlot = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(enumType: DayPeriod::class)]
    private ?DayPeriod $period = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(
        enumType: GameSessionStatus::class,
    )]
    private GameSessionStatus $status = GameSessionStatus::SCHEDULED;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(?Campaign $campaign): static
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getCalendarSlot(): ?CalendarSlot
    {
        return $this->calendarSlot;
    }

    public function setCalendarSlot(?CalendarSlot $calendarSlot): static
    {
        $this->calendarSlot = $calendarSlot;

        return $this;
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

    public function getPeriod(): ?DayPeriod
    {
        return $this->period;
    }

    public function setPeriod(DayPeriod $period): static
    {
        $this->period = $period;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isScheduled(): bool
    {
        return $this->status === GameSessionStatus::SCHEDULED;
    }

    public function isCancelled(): bool
    {
        return $this->status === GameSessionStatus::CANCELLED;
    }

    public function cancel(): static
    {
        $this->status = GameSessionStatus::CANCELLED;

        return $this;
    }
}
