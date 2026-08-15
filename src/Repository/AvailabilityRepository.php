<?php

namespace App\Repository;

use App\Entity\Availability;
use App\Entity\CalendarSlot;
use App\Entity\Participant;
use App\Entity\Campaign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Availability>
 */
class AvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Availability::class);
    }

    /**
     * @return list<Availability>
     */
    public function findByCampaignAndWeek(
        Campaign $campaign,
        \DateTimeImmutable $weekStart,
    ): array {
        $weekEnd = $weekStart->modify('+7 days');

        return $this->createQueryBuilder('availability')
            ->join('availability.calendarSlot', 'slot')
            ->join('availability.participant', 'participant')
            ->andWhere('slot.campaign = :campaign')
            ->andWhere('slot.date >= :weekStart')
            ->andWhere('slot.date < :weekEnd')
            ->andWhere('participant.archivedAt IS NULL')
            ->setParameter('campaign', $campaign)
            ->setParameter('weekStart', $weekStart)
            ->setParameter('weekEnd', $weekEnd)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param list<CalendarSlot> $slots
     *
     * @return list<Availability>
     */
    public function findByParticipantAndSlots(
        Participant $participant,
        array $slots,
    ): array {
        if ($slots === []) {
            return [];
        }

        return $this->createQueryBuilder('availability')
            ->andWhere('availability.participant = :participant')
            ->andWhere('availability.calendarSlot IN (:slots)')
            ->setParameter('participant', $participant)
            ->setParameter('slots', $slots)
            ->getQuery()
            ->getResult();
    }

    public function existsByParticipant(Participant $participant): bool
    {
        return (bool) $this->createQueryBuilder('availability')
            ->select('COUNT(availability.id)')
            ->andWhere('availability.participant = :participant')
            ->setParameter('participant', $participant)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
