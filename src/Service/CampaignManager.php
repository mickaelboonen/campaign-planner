<?php

namespace App\Service;

use App\Entity\Campaign;
use App\Entity\User;
use App\DTO\CreateCampaignData;
use App\DTO\EditCampaignData;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CampaignManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CampaignImageManager $campaignImageManager,
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
        $campaign->archive();

        $this->entityManager->flush();
    }

    public function restore(Campaign $campaign): void
    {
        $campaign->restore();

        $this->entityManager->flush();
    }
}
