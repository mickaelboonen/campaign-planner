<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Campaign;
use App\Entity\GameSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameSession>
 */
class GameSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameSession::class);
    }

    public function findUpcomingByCampaign(
        Campaign $campaign,
    ): array {
        return $this->createQueryBuilder('session')
            ->andWhere('session.campaign = :campaign')
            ->andWhere('session.date >= :today')
            ->setParameter('campaign', $campaign)
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->orderBy('session.date', 'ASC')
            ->addOrderBy('session.period', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPastByCampaign(
        Campaign $campaign,
    ): array {
        return $this->createQueryBuilder('session')
            ->andWhere('session.campaign = :campaign')
            ->andWhere('session.date < :today')
            ->setParameter('campaign', $campaign)
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->orderBy('session.date', 'DESC')
            ->addOrderBy('session.period', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUpcomingByOwner(
        User $owner,
    ): array {
        return $this->createQueryBuilder('session')
            ->join('session.campaign', 'campaign')
            ->andWhere('campaign.owner = :owner')
            ->andWhere('session.date >= :today')
            ->setParameter('owner', $owner)
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->orderBy('session.date', 'ASC')
            ->addOrderBy('session.period', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
