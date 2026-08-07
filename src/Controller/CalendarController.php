<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Entity\CalendarSlot;
use App\Security\Voter\CampaignVoter;
use App\Service\CalendarSlotManager;
use App\Service\SessionManager;
use App\Repository\AvailabilityRepository;
use App\Service\CalendarViewBuilder;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

        $currentWeekStart = (new \DateTimeImmutable())
            ->modify('monday this week')
            ->setTime(0, 0);

        $availableWeeks = [];

        $limit = $currentWeekStart->modify('+6 months');

        for (
            $week = $currentWeekStart;
            $week <= $limit;
            $week = $week->modify('+1 week')
        ) {
            $availableWeeks[] = [
                'start' => $week,
                'end' => $week->modify('+6 days'),
            ];
        }

        $isPastWeek = $weekStart < $currentWeekStart;

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
            'isPastWeek' => $isPastWeek,
            'availableWeeks' => $availableWeeks,
        ]);
    }

    #[Route('/slots/save', name: 'save_slots', methods: ['POST'])]
    public function saveSlots(
        Request $request,
        Campaign $campaign,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(
            CampaignVoter::EDIT,
            $campaign,
        );

        if (!$this->isCsrfTokenValid(
            'save-calendar-slots-'.$campaign->getId(),
            (string) $request->request->get('_token'),
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.',
            );
        }

        $week = (string) $request->request->get('week');

        try {
            $weekStart = new \DateTimeImmutable($week);
        } catch (\Exception) {
            throw $this->createNotFoundException(
                'La semaine envoyée est invalide.',
            );
        }

        $weekStart = $weekStart
            ->modify('monday this week')
            ->setTime(0, 0);

        $currentWeekStart = (new \DateTimeImmutable())
            ->modify('monday this week')
            ->setTime(0, 0);

        if ($weekStart < $currentWeekStart) {
            throw $this->createAccessDeniedException(
                'Une semaine passée ne peut plus être modifiée.',
            );
        }

        try {
            $this->calendarSlotManager->saveBlockingStates(
                $campaign,
                $request->request->all('slots'),
            );
        } catch (\InvalidArgumentException $exception) {
            throw $this->createNotFoundException(
                $exception->getMessage(),
            );
        }

        $this->addFlash(
            'success',
            'Les créneaux ont bien été mis à jour.',
        );

        return $this->redirectToRoute('calendar_show', [
            'id' => $campaign->getId(),
            'week' => $weekStart->format('Y-m-d'),
        ]);
    }

    #[Route( '/slot/{slot}/schedule', name: 'slot_schedule', methods: ['POST'])]
    public function schedule(
        Campaign $campaign,
        CalendarSlot $slot,
        Request $request,
        SessionManager $sessionManager,
    ): Response
    {
        try {
            $sessionManager->scheduleFromSlot(
                $slot,
            );

            $this->addFlash(
                'success',
                'La session a bien été créée.',
            );
        } catch (\DomainException $exception) {
            $this->addFlash(
                'error',
                $exception->getMessage(),
            );
        }

        return $this->redirectToRoute(
            'calendar_show',
            [
                'id' => $slot->getCampaign()->getId(),
                'week' => $slot->getDate()->format('Y-m-d'),
            ],
        );
    }
}
