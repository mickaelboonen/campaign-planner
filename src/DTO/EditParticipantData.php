<?php

namespace App\DTO;

use App\Entity\Participant;
use Symfony\Component\Validator\Constraints as Assert;

final class EditParticipantData
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public ?string $email = null;

    #[Assert\Regex(
        pattern: '/^\d{10}$/',
        message: 'Le numéro de téléphone doit contenir exactement 10 chiffres.',
    )]
    public ?string $phone = null;

    #[Assert\Length(max: 255)]
    public ?string $characterName = null;

    public static function fromParticipant(Participant $participant): self
    {
        $data = new self();

        $data->name = $participant->getName();
        $data->email = $participant->getEmail();
        $data->phone = $participant->getPhone();
        $data->characterName = $participant->getCharacterName();

        return $data;
    }
}