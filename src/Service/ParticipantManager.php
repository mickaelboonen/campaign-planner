<?php

namespace App\Service;

use App\DTO\CreateParticipantData;
use App\DTO\EditParticipantData;
use App\Repository\ParticipantRepository;
use App\Entity\Campaign;
use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ParticipantManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ParticipantRepository $participantRepository,
    ) {
    }

    public function create(
        Campaign $campaign,
        CreateParticipantData $data,
    ): Participant {
        $participant = new Participant();

        $participant->setCampaign($campaign);
        $participant->setName($data->name);
        $participant->setEmail($data->email);
        $participant->setPhone($data->phone);
        $participant->setCharacterName($data->characterName);
        $participant->setAccessToken(bin2hex(random_bytes(32)));

        $this->entityManager->persist($participant);
        $this->entityManager->flush();

        return $participant;
    }

    public function update(
        Participant $participant,
        EditParticipantData $data,
    ): void {
        $participant->setName((string) $data->name);
        $participant->setEmail((string) $data->email);
        $participant->setPhone($data->phone);
        $participant->setCharacterName($data->characterName);

        $this->entityManager->flush();
    }

    public function archive(Participant $participant): void
    {
        $participant->archive();

        $this->entityManager->flush();
    }

    public function restore(Participant $participant): void
    {
        $participant->restore();

        $this->entityManager->flush();
    }
}