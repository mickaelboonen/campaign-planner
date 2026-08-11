<?php

namespace App\Repository;

use App\Entity\Feedback;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
    public function findForAdmin(): array
    {
        return $this->createQueryBuilder('feedback')
            ->leftJoin('feedback.user', 'user')
            ->addSelect('user')
            ->orderBy('feedback.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
