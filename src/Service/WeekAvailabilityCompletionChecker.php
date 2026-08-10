<?php

namespace App\Service;

use App\Entity\Campaign;
use App\Repository\AvailabilityRepository;
use App\Repository\ParticipantRepository;

final readonly class WeekAvailabilityCompletionChecker
{
    public function __construct(
        private ParticipantRepository $participantRepository,
        private AvailabilityRepository $availabilityRepository,
    ) {
    }

    public function isComplete(
        Campaign $campaign,
        \DateTimeImmutable $weekStart,
    ): bool {
        $participants = $this->participantRepository
            ->findActiveByCampaign($campaign);

        if ($participants === []) {
            return false;
        }

        $availabilities = $this->availabilityRepository
            ->findByCampaignAndWeek(
                $campaign,
                $weekStart,
            );

        $answeredParticipantIds = [];

        foreach ($availabilities as $availability) {
            $participantId = $availability
                ->getParticipant()
                ->getId();

            if ($participantId !== null) {
                $answeredParticipantIds[$participantId] = true;
            }
        }

        foreach ($participants as $participant) {
            $participantId = $participant->getId();

            if (
                $participantId === null
                || !isset($answeredParticipantIds[$participantId])
            ) {
                return false;
            }
        }

        return true;
    }
}
