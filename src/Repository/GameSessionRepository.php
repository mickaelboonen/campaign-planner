<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Campaign;
use App\Entity\GameSession;
use App\Enum\GameSessionStatus;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<GameSession>
 */
class GameSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameSession::class);
    }

    public function findUpcomingByCampaign(Campaign $campaign): array
    {
        return $this->createQueryBuilder('session')
            ->andWhere('session.campaign = :campaign')
            ->andWhere('session.date >= :today')
            ->andWhere('session.status = :status')
            ->setParameter('status', GameSessionStatus::SCHEDULED)
            ->setParameter('campaign', $campaign)
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->orderBy('session.date', 'ASC')
            ->addOrderBy('session.period', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPastByCampaign(Campaign $campaign): array
    {
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

    public function findUpcomingByOwner(User $owner): array
    {
        return $this->createQueryBuilder('session')
            ->join('session.campaign', 'campaign')
            ->andWhere('campaign.owner = :owner')
            ->andWhere('session.date >= :today')
            ->andWhere('session.status = :status')
            ->setParameter('status', GameSessionStatus::SCHEDULED)
            ->setParameter('owner', $owner)
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->orderBy('session.date', 'ASC')
            ->addOrderBy('session.period', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findSessionsNeedingReminder(\DateTimeImmutable $today): array
    {
        $limit = $today->modify('+7 days');
        return $this->createQueryBuilder('session')
            ->andWhere('session.date > :today')
            ->andWhere('session.date <= :limit')
            ->andWhere('session.status = :status')
            ->andWhere('session.reminderSentAt IS NULL')
            ->setParameter('today', $today->format('Y-m-d'))
            ->setParameter('limit', $limit->format('Y-m-d'))
            ->setParameter('status',GameSessionStatus::SCHEDULED)
            ->orderBy('session.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPastScheduledSessions(\DateTimeImmutable $today): array
    {
        return $this->createQueryBuilder('session')
            ->andWhere('session.date < :today')
            ->andWhere('session.status = :status')
            ->setParameter('today',$today->setTime(0, 0))
            ->setParameter('status',GameSessionStatus::SCHEDULED)
            ->getQuery()
            ->getResult();
    }

}
