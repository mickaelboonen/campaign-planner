<?php

namespace App\Repository;

use App\Entity\Feedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Enum\FeedbackStatus;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Feedback>
 */
final class FeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feedback::class);
    }

    public function createAdminQuery(string $status = 'open'): QueryBuilder
    {
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

        return $qb;
    }

    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('feedback')
            ->select('feedback.status AS status, COUNT(feedback.id) AS total')
            ->groupBy('feedback.status')
            ->getQuery()
            ->getArrayResult();

        $counts = [
            'new' => 0,
            'read' => 0,
            'closed' => 0,
        ];

        foreach ($rows as $row) {
            $counts[$row['status']->value] = (int) $row['total'];
        }

        $counts['open'] = $counts['new'] + $counts['read'];
        $counts['all'] = $counts['open'] + $counts['closed'];

        return $counts;
    }
}
