<?php

namespace App\Repository;

use App\Entity\CalendarSlot;
use App\Entity\Campaign;
use App\Enum\DayPeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CalendarSlot>
 */
final class CalendarSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarSlot::class);
    }

    public function findOneByCampaignDateAndPeriod(
        Campaign $campaign,
        \DateTimeImmutable $date,
        DayPeriod $period,
    ): ?CalendarSlot {
        return $this->findOneBy([
            'campaign' => $campaign,
            'date' => $date,
            'period' => $period,
        ]);
    }

    /**
     * @return list<CalendarSlot>
     */
    public function findByCampaignAndWeek(
        Campaign $campaign,
        \DateTimeImmutable $weekStart,
    ): array {
        $weekEnd = $weekStart->modify('+7 days');

        return $this->createQueryBuilder('slot')
            ->andWhere('slot.campaign = :campaign')
            ->andWhere('slot.date >= :weekStart')
            ->andWhere('slot.date < :weekEnd')
            ->setParameter('campaign', $campaign)
            ->setParameter('weekStart', $weekStart)
            ->setParameter('weekEnd', $weekEnd)
            ->orderBy('slot.date', 'ASC')
            ->addOrderBy('slot.period', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param list<int> $ids
     *
     * @return list<CalendarSlot>
     */
    public function findByIdsAndCampaign(
        array $ids,
        Campaign $campaign,
    ): array {
        if ($ids === []) {
            return [];
        }

        return $this->createQueryBuilder('slot')
            ->andWhere('slot.id IN (:ids)')
            ->andWhere('slot.campaign = :campaign')
            ->setParameter('ids', $ids)
            ->setParameter('campaign', $campaign)
            ->getQuery()
            ->getResult();
    }
}