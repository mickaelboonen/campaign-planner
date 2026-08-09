<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    public const TYPE_AVAILABILITY_UPDATED = 'availability_updated';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $gm = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campaign $campaign = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $participant = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column]
    private ?int $count = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $weekStart = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $readAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $seenAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function isRead(): bool
    {
        return $this->readAt !== null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGm(): ?User
    {
        return $this->gm;
    }

    public function setGm(?User $gm): static
    {
        $this->gm = $gm;

        return $this;
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

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): static
    {
        $this->participant = $participant;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): static
    {
        $this->count = $count;

        return $this;
    }

    public function getWeekStart(): ?\DateTimeImmutable
    {
        return $this->weekStart;
    }

    public function setWeekStart(\DateTimeImmutable $weekStart): static
    {
        $this->weekStart = $weekStart;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeImmutable $readAt): static
    {
        $this->readAt = $readAt;

        return $this;
    }

    public function getSeenAt(): ?\DateTimeImmutable
    {
        return $this->seenAt;
    }

    public function setSeenAt(?\DateTimeImmutable $seenAt): static
    {
        $this->seenAt = $seenAt;

        return $this;
    }

    public function isSeen(): bool
    {
        return $this->seenAt !== null;
    }

    public function markAsSeen(): static
    {
        if ($this->seenAt === null) {
            $this->seenAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function markAsRead(): static
    {
        if ($this->readAt === null) {
            $this->readAt = new \DateTimeImmutable();
        }

        if ($this->seenAt === null) {
            $this->seenAt = $this->readAt;
        }

        return $this;
    }
}
