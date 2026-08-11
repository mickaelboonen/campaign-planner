<?php

namespace App\Repository;

use App\Entity\Feedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Enum\FeedbackStatus;

/**
 * @extends ServiceEntityRepository<Feedback>
 */
final class FeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feedback::class);
    }

    /**
     * @return list<Feedback>
     */
    public function findForAdmin(
        string $status = 'open',
    ): array {
        $qb = $this->createQueryBuilder('feedback')
            ->leftJoin('feedback.user', 'user')
            ->addSelect('user')
            ->orderBy('feedback.createdAt', 'DESC');

        match ($status) {
            'new' => $qb->andWhere('feedback.status = :status')
                ->setParameter('status', FeedbackStatus::NEW),

            'read' => $qb->andWhere('feedback.status = :status')
                ->setParameter('status', FeedbackStatus::READ),

            'closed' => $qb->andWhere('feedback.status = :status')
                ->setParameter('status', FeedbackStatus::CLOSED),

            'all' => null,

            default => $qb
                ->andWhere('feedback.status != :closed')
                ->setParameter('closed', FeedbackStatus::CLOSED),
        };

        return $qb
            ->getQuery()
            ->getResult();
    }
}
