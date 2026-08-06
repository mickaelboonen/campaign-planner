<?php

namespace App\Service;

use App\Entity\Campaign;
use App\Entity\User;
use App\DTO\CreateCampaignData;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CampaignManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(
        User $owner,
        CreateCampaignData $data,
    ): Campaign {
        $campaign = new Campaign();
        $campaign->setOwner($owner);
        $campaign->setName($data->name);
        $campaign->setColor($data->color);

        $this->entityManager->persist($campaign);
        $this->entityManager->flush();

        return $campaign;
    }

    public function archive(Campaign $campaign): void
    {
        $campaign->archive();

        $this->entityManager->flush();
    }

    public function restore(Campaign $campaign): void
    {
        $campaign->restore();

        $this->entityManager->flush();
    }
}