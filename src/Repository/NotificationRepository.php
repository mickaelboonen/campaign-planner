<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @return Notification[]
     */
    public function findRecentByGm(
        User $gm,
        int $limit = 10,
    ): array {
        return $this->createQueryBuilder('notification')
            ->andWhere('notification.gm = :gm')
            ->setParameter('gm', $gm)
            ->orderBy('notification.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countUnreadByGm(User $gm): int
    {
        return (int) $this->createQueryBuilder('notification')
            ->select('COUNT(notification.id)')
            ->andWhere('notification.gm = :gm')
            ->andWhere('notification.readAt IS NULL')
            ->setParameter('gm', $gm)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countUnseenByGm(User $gm): int
    {
        return (int) $this->createQueryBuilder('notification')
            ->select('COUNT(notification.id)')
            ->andWhere('notification.gm = :gm')
            ->andWhere('notification.seenAt IS NULL')
            ->setParameter('gm', $gm)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Notification[]
     */
    public function findUnreadByGm(
        User $gm,
        int $limit = 10,
    ): array {
        return $this->createQueryBuilder('notification')
            ->andWhere('notification.gm = :gm')
            ->andWhere('notification.readAt IS NULL')
            ->setParameter('gm', $gm)
            ->orderBy('notification.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
