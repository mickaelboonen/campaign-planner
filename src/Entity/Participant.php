<?php

namespace App\Entity;

use App\Entity\Traits\ArchivableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\UniqueConstraint(
    name: 'uniq_participant_campaign_email',
    columns: ['campaign_id', 'email'],
)]
#[ORM\HasLifecycleCallbacks]
class Participant
{
    use TimestampableTrait;
    use ArchivableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $characterName = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $accessToken = null;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campaign $campaign = null;

    public function __construct()
    {
        $this->initializeTimestamps();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower(trim($email));

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $phone = $phone !== null ? trim($phone) : null;

        $this->phone = $phone !== '' ? $phone : null;

        return $this;
    }

    public function getCharacterName(): ?string
    {
        return $this->characterName;
    }

    public function setCharacterName(?string $characterName): static
    {
        $characterName = $characterName !== null
            ? trim($characterName)
            : null;

        $this->characterName = $characterName !== ''
            ? $characterName
            : null;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
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
}