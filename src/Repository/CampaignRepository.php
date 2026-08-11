<?php

namespace App\Repository;

use App\Entity\Campaign;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;


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

    public function createAdminQuery(string $status = 'active', string $search = ''): QueryBuilder
    {
        $qb = $this->createQueryBuilder('campaign')
            ->leftJoin('campaign.owner', 'owner')
            ->addSelect('owner')
            ->orderBy('campaign.createdAt', 'DESC');

        match ($status) {
            'archived' => $qb->andWhere('campaign.archivedAt IS NOT NULL'),
            'all' => null,
            default => $qb->andWhere('campaign.archivedAt IS NULL'),
        };

        if ($search !== '') {
            $qb
                ->andWhere('LOWER(campaign.name) LIKE :search OR LOWER(owner.email) LIKE :search')
                ->setParameter('search', '%'.mb_strtolower($search).'%');
        }

        return $qb;
    }
}
