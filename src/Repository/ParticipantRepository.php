<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Campaign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participant>
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }
    
    /**
     * @return list<Participant>
     */
    public function findActiveByCampaign(Campaign $campaign): array
    {
        return $this->createQueryBuilder('participant')
            ->andWhere('participant.campaign = :campaign')
            ->andWhere('participant.archivedAt IS NULL')
            ->setParameter('campaign', $campaign)
            ->orderBy('participant.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function existsByCampaignAndEmail(
        Campaign $campaign,
        string $email,
    ): bool {
        return (bool) $this->createQueryBuilder('participant')
            ->select('COUNT(participant.id)')
            ->andWhere('participant.campaign = :campaign')
            ->andWhere('LOWER(participant.email) = :email')
            ->setParameter('campaign', $campaign)
            ->setParameter('email', mb_strtolower(trim($email)))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function existsByCampaignAndEmailExcludingParticipant(
        Campaign $campaign,
        string $email,
        Participant $participant,
    ): bool {
        return (bool) $this->createQueryBuilder('participant')
            ->select('COUNT(participant.id)')
            ->andWhere('participant.campaign = :campaign')
            ->andWhere('LOWER(participant.email) = :email')
            ->andWhere('participant != :participant')
            ->setParameter('campaign', $campaign)
            ->setParameter('email', mb_strtolower(trim($email)))
            ->setParameter('participant', $participant)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
