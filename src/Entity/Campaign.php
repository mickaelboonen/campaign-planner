<?php

namespace App\Entity;

use App\Repository\CampaignRepository;
use App\Entity\Traits\ArchivableTrait;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampaignRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Campaign
{
    use TimestampableTrait;
    use ArchivableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $color = null;

    #[ORM\ManyToOne(inversedBy: 'campaigns')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * @var Collection<int, Participant>
     */
    #[ORM\OneToMany(targetEntity: Participant::class, mappedBy: 'campaign')]
    private Collection $participants;

    /**
     * @var Collection<int, CalendarSlot>
     */
    #[ORM\OneToMany(targetEntity: CalendarSlot::class, mappedBy: 'campaign')]
    private Collection $calendarSlots;

    public function __construct()
    {
        $this->initializeTimestamps();
        $this->participants = new ArrayCollection();
        $this->calendarSlots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setCampaign($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, CalendarSlot>
     */
    public function getCalendarSlots(): Collection
    {
        return $this->calendarSlots;
    }

    public function addCalendarSlot(CalendarSlot $calendarSlot): static
    {
        if (!$this->calendarSlots->contains($calendarSlot)) {
            $this->calendarSlots->add($calendarSlot);
            $calendarSlot->setCampaign($this);
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }
}
