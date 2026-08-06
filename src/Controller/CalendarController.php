<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Security\Voter\CampaignVoter;
use App\Service\CalendarSlotManager;
use App\Repository\AvailabilityRepository;
use App\Service\CalendarViewBuilder;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/campaign/{id}/calendar', name: 'calendar_')]
final class CalendarController extends BaseController
{
    public function __construct(
        private readonly CalendarSlotManager $calendarSlotManager,
        private readonly CalendarViewBuilder $calendarViewBuilder,
        private readonly ParticipantRepository $participantRepository,
        private readonly AvailabilityRepository $availabilityRepository,
    ) {
    }

    #[Route('', name: 'show', methods: ['GET'])]
    public function show(
        Request $request,
        Campaign $campaign,
    ): Response {
        $this->denyAccessUnlessGranted(
            CampaignVoter::VIEW,
            $campaign,
        );

        $requestedWeek = $request->query->get('week');

        try {
            $date = $requestedWeek
                ? new \DateTimeImmutable($requestedWeek)
                : new \DateTimeImmutable();
        } catch (\Exception) {
            throw $this->createNotFoundException(
                'La semaine demandée est invalide.',
            );
        }

        $weekStart = $date
            ->modify('monday this week')
            ->setTime(0, 0);

        $slots = $this->calendarSlotManager->getOrCreateWeek(
            $campaign,
            $weekStart,
        );

        $participants = $this->participantRepository
            ->findActiveByCampaign($campaign);

        $availabilities = $this->availabilityRepository
            ->findByCampaignAndWeek($campaign, $weekStart);

        $calendar = $this->calendarViewBuilder->build(
            weekStart: $weekStart,
            slots: $slots,
            participants: $participants,
            availabilities: $availabilities,
        );

        return $this->render('calendar/show.html.twig', [
            'campaign' => $campaign,
            'calendar' => $calendar,
        ]);
    }
}