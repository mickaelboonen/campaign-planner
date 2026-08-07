<?php

namespace App\Controller;

use App\Repository\AvailabilityRepository;
use App\Repository\ParticipantRepository;
use App\Service\CalendarSlotManager;
use App\Service\CalendarViewBuilder;
use App\Service\AvailabilityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/availability', name: 'participant_availability_')]
final class ParticipantAccessController extends AbstractController
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly AvailabilityRepository $availabilityRepository,
        private readonly CalendarSlotManager $calendarSlotManager,
        private readonly CalendarViewBuilder $calendarViewBuilder,
        private readonly AvailabilityManager $availabilityManager,
    ) {
    }

    #[Route('/{token}', name: 'show', methods: ['GET'])]
    public function show(
        Request $request,
        string $token,
    ): Response {
        $participant = $this->participantRepository
            ->findActiveByAccessToken($token);

        if ($participant === null) {
            throw $this->createNotFoundException(
                'Ce lien de participation est invalide ou n’est plus actif.',
            );
        }

        $campaign = $participant->getCampaign();

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

        return $this->render('participant_access/calendar.html.twig', [
            'participant' => $participant,
            'campaign' => $campaign,
            'calendar' => $calendar,
            'isPastWeek' => $isPastWeek,
            'availableWeeks' => $availableWeeks,
        ]);
    }

    #[Route('/{token}/save', name: 'save', methods: ['POST'])]
    public function save(
        Request $request,
        string $token,
    ): RedirectResponse {
        $participant = $this->participantRepository
            ->findActiveByAccessToken($token);

        if ($participant === null) {
            throw $this->createNotFoundException(
                'Ce lien de participation est invalide ou n’est plus actif.',
            );
        }

        if (!$this->isCsrfTokenValid(
            'save-availabilities-'.$participant->getId(),
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

        $submittedAvailabilities = $request->request->all(
            'availabilities',
        );

        try {
            $this->availabilityManager->save(
                $participant,
                $submittedAvailabilities,
            );
        } catch (\InvalidArgumentException $exception) {
            throw $this->createNotFoundException(
                $exception->getMessage(),
            );
        }

        $this->addFlash(
            'success',
            'Vos disponibilités ont bien été enregistrées.',
        );

        return $this->redirectToRoute(
            'participant_availability_show',
            [
                'token' => $participant->getAccessToken(),
                'week' => $weekStart->format('Y-m-d'),
            ],
        );
    }
}
