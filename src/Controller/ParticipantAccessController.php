<?php

namespace App\Controller;

use App\Repository\AvailabilityRepository;
use App\Repository\GameSessionRepository;
use App\Repository\ParticipantRepository;
use App\Service\AvailabilityManager;
use App\Service\CalendarSlotManager;
use App\Service\CalendarViewBuilder;
use App\Controller\BaseController;
use App\Service\NotificationManager;
use App\Service\WeekAvailabilityCompletionChecker;
use App\Service\Notification\EmailAvailabilityCompletionNotifier;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/p/{token}', name: 'participant_')]
final class ParticipantAccessController extends BaseController
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly AvailabilityRepository $availabilityRepository,
        private readonly GameSessionRepository $gameSessionRepository,
        private readonly CalendarSlotManager $calendarSlotManager,
        private readonly CalendarViewBuilder $calendarViewBuilder,
        private readonly AvailabilityManager $availabilityManager,
        private readonly NotificationManager $notificationManager,
        private readonly WeekAvailabilityCompletionChecker $weekCompletionChecker,
        private readonly EmailAvailabilityCompletionNotifier $emailAvailabilityCompletionNotifier,
    ) {
    }

    #[Route(
        '/availability',
        name: 'availability_show',
        methods: ['GET'],
    )]
    public function show(
        Request $request,
        string $token,
    ): Response {
        $participant = $this->participantRepository->findActiveByAccessToken($token);

        if ($participant === null) {
            throw $this->createNotFoundException(
                'Ce lien de participation est invalide ou n’est plus actif.',
            );
        }

        $campaign = $participant->getCampaign();

        $this->denyArchivedCampaign(
            $campaign,
            'Le calendrier de cette campagne n’est plus disponible.',
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
            ->findByCampaignAndWeek(
                $campaign,
                $weekStart,
            );

        $calendar = $this->calendarViewBuilder->build(
            weekStart: $weekStart,
            slots: $slots,
            participants: $participants,
            availabilities: $availabilities,
        );

        return $this->render(
            'participant_access/calendar.html.twig',
            [
                'participant' => $participant,
                'campaign' => $campaign,
                'calendar' => $calendar,
                'isPastWeek' => $isPastWeek,
                'availableWeeks' => $availableWeeks,
            ],
        );
    }

    #[Route(
        '/availability/save',
        name: 'availability_save',
        methods: ['POST'],
    )]
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

        $campaign = $participant->getCampaign();

        $this->denyArchivedCampaign(
            $campaign,
            'Le calendrier de cette campagne n’est plus disponible.',
        );


        if (!$this->isCsrfTokenValid(
            'save-availabilities-' . $participant->getId(),
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

        $submittedAvailabilities = $request->request->all('availabilities');

        $wasComplete = $this->weekCompletionChecker->isComplete(
            $campaign,
            $weekStart,
        );

        try {
            $changedCount = $this->availabilityManager->save($participant, $submittedAvailabilities);

            $this->notificationManager->notifyAvailabilityUpdated($participant, $weekStart, $changedCount);

        } catch (\InvalidArgumentException $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }

        $isComplete = $this->weekCompletionChecker->isComplete(
            $campaign,
            $weekStart,
        );

        if (!$wasComplete && $isComplete) {
            $this->emailAvailabilityCompletionNotifier->notify(
                $campaign,
                $weekStart,
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

    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function dashboard(
        string $token,
    ): Response {
        $participations = $this->participantRepository
            ->findActiveByDashboardToken($token);

        if ($participations === []) {
            throw $this->createNotFoundException(
                'Ce lien joueur est invalide ou n’est plus actif.',
            );
        }

        $referenceParticipant = $participations[0];

        $campaigns = [];
        $upcomingSessions = [];

        foreach ($participations as $participation) {
            $campaign = $participation->getCampaign();

            $campaigns[] = [
                'participant' => $participation,
                'campaign' => $campaign,
            ];

            foreach (
                $this->gameSessionRepository
                    ->findUpcomingByCampaign($campaign)
                as $session
            ) {
                $upcomingSessions[] = $session;
            }
        }

        usort(
            $upcomingSessions,
            static fn ($a, $b) =>
                $a->getDate() <=> $b->getDate(),
        );

        return $this->render(
            'participant_access/dashboard.html.twig',
            [
                'participant' => $referenceParticipant,
                'participations' => $campaigns,
                'nextSession' => $upcomingSessions[0] ?? null,
                'upcomingSessions' => array_slice(
                    $upcomingSessions,
                    1,
                ),
            ],
        );
    }
}
