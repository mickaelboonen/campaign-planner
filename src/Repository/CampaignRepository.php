<?php

namespace App\Repository;

use App\Entity\Campaign;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Campaign>
 */
class CampaignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campaign::class);
    }

    /**
     * @return list<Campaign>
     */
    public function findActiveByOwner(User $owner): array
    {
        return $this->createQueryBuilder('campaign')
            ->andWhere('campaign.owner = :owner')
            ->andWhere('campaign.archivedAt IS NULL')
            ->setParameter('owner', $owner)
            ->orderBy('campaign.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}