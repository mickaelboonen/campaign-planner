<?php

namespace App\Service;

use App\Entity\Campaign;
use App\Entity\User;
use App\DTO\CreateCampaignData;
use App\DTO\EditCampaignData;
use App\Repository\GameSessionRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CampaignManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CampaignImageManager $campaignImageManager,
        private GameSessionRepository $gameSessionRepository,
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

        if ($data->imageFile !== null) {
            $this->campaignImageManager->upload(
                $campaign,
                $data->imageFile,
            );
        }

        $this->entityManager->persist($campaign);
        $this->entityManager->flush();

        return $campaign;
    }

    public function update(
        Campaign $campaign,
        EditCampaignData $data,
    ): void {
        $campaign->setName($data->name);
        $campaign->setColor($data->color);

        if ($data->imageFile !== null) {
            $this->campaignImageManager->upload(
                $campaign,
                $data->imageFile,
            );
        }

        $this->entityManager->flush();
    }

    public function archive(Campaign $campaign): void
    {
        if ($campaign->isArchived()) {
            throw new \DomainException(
                'Cette campagne est déjà archivée.',
            );
        }

        $upcomingSessions = $this->gameSessionRepository
            ->findUpcomingByCampaign($campaign);

        if ($upcomingSessions !== []) {
            throw new \DomainException(
                'Cette campagne possède encore des sessions à venir.',
            );
        }

        $campaign->archive();

        $this->entityManager->flush();
    }

    public function restore(Campaign $campaign): void
    {
        $campaign->restore();

        $this->entityManager->flush();
    }
}
