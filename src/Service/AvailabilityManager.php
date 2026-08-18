<?php

namespace App\Service;

use App\Entity\Availability;
use App\Entity\Participant;
use App\Enum\AvailabilityStatus;
use App\Repository\AvailabilityRepository;
use App\Repository\CalendarSlotRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AvailabilityManager
{
    public function __construct(
        private CalendarSlotRepository $calendarSlotRepository,
        private AvailabilityRepository $availabilityRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array<string|int, mixed> $submittedAvailabilities
     */
    public function save(
        Participant $participant,
        array $submittedAvailabilities,
    ): int {
        $normalizedValues = $this->normalizeValues($submittedAvailabilities);
        $slotIds = array_keys($normalizedValues);

        $slots = $this->calendarSlotRepository->findByIdsAndCampaign(
            $slotIds,
            $participant->getCampaign(),
        );

        /*
         * Empêche un utilisateur de transmettre manuellement
         * l’identifiant d’un créneau appartenant à une autre campagne.
         */
        if (count($slots) !== count($slotIds)) {
            throw new \InvalidArgumentException(
                'availability.invalid_slots',
            );
        }

        $existingAvailabilities = $this->availabilityRepository
            ->findByParticipantAndSlots($participant, $slots);

        $availabilityBySlotId = [];

        foreach ($existingAvailabilities as $availability) {
            $slotId = $availability->getCalendarSlot()->getId();

            if ($slotId !== null) {
                $availabilityBySlotId[$slotId] = $availability;
            }
        }

        $changedCount = 0;

        foreach ($slots as $slot) {
            $slotId = $slot->getId();

            if ($slotId === null) {
                continue;
            }

            /*
             * Un créneau bloqué ne doit pas être modifiable,
             * même avec une requête fabriquée manuellement.
             */
            if ($slot->isBlocked()) {
                continue;
            }

            $status = $normalizedValues[$slotId];
            $availability = $availabilityBySlotId[$slotId] ?? null;

            /*
             * Une valeur vide représente l’absence de réponse.
             */
            if ($status === null) {
                if ($availability !== null) {
                    $this->entityManager->remove($availability);
                    ++$changedCount;
                }

                continue;
            }

            if ($availability === null) {
                $availability = new Availability();
                $availability->setParticipant($participant);
                $availability->setCalendarSlot($slot);
                $availability->setStatus($status);

                $this->entityManager->persist($availability);
                ++$changedCount;

                continue;
            }

            if ($availability->getStatus() !== $status) {
                $availability->setStatus($status);
                ++$changedCount;
            }
        }

        $this->entityManager->flush();

        return $changedCount;
    }

    /**
     * @param array<string|int, mixed> $submittedAvailabilities
     *
     * @return array<int, AvailabilityStatus|null>
     */
    private function normalizeValues(
        array $submittedAvailabilities,
    ): array {
        $normalized = [];

        foreach ($submittedAvailabilities as $slotId => $value) {
            $slotId = filter_var($slotId, FILTER_VALIDATE_INT);

            if ($slotId === false || $slotId <= 0) {
                throw new \InvalidArgumentException(
                    'availability.invalid_slot_id',
                );
            }

            if ($value === '') {
                $normalized[$slotId] = null;
                continue;
            }

            if (!is_string($value)) {
                throw new \InvalidArgumentException(
                    'availability.invalid_status',
                );
            }

            $status = AvailabilityStatus::tryFrom($value);

            if ($status === null) {
                throw new \InvalidArgumentException(
                    'availability.unknown_status',
                );
            }

            $normalized[$slotId] = $status;
        }

        return $normalized;
    }
}
