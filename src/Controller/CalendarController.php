<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Repository\AvailabilityRepository;
use App\Repository\ParticipantRepository;
use App\Security\Voter\CampaignVoter;
use App\Service\CalendarSlotManager;
use App\Service\CalendarViewBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        TranslatorInterface $translator,
    ): Response {
        $this->denyAccessUnlessGranted(CampaignVoter::VIEW, $campaign);

        $this->denyArchivedCampaign(
            $campaign,
            $translator->trans(
                'controller.campaign.calendar_unavailable',
                domain: 'error',
            ),
        );

        $requestedWeek = $request->query->get('week');

        try {
            $date = $requestedWeek
                ? new \DateTimeImmutable($requestedWeek)
                : new \DateTimeImmutable();
        } catch (\Exception) {
            throw $this->createNotFoundException(
                $translator->trans(
                    'controller.calendar.invalid_requested_week',
                    domain: 'error',
                ),
            );
        }

        $weekStart = $date->modify('monday this week')->setTime(0, 0);
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
        TranslatorInterface $translator,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(CampaignVoter::EDIT, $campaign);

        $this->denyInvalidCsrf(
            'save-calendar-slots-'.$campaign->getId(),
            $request->request->get('_token'),
            $translator,
        );

        $week = (string) $request->request->get('week');

        try {
            $weekStart = new \DateTimeImmutable($week);
        } catch (\Exception) {
            throw $this->createNotFoundException(
                $translator->trans(
                    'controller.calendar.invalid_submitted_week',
                    domain: 'error',
                ),
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
                $translator->trans(
                    'controller.calendar.past_week',
                    domain: 'error',
                ),
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

        $this->addFlash('success', 'calendar.slots_updated');

        return $this->redirectToRoute('calendar_show', [
            'id' => $campaign->getId(),
            'week' => $weekStart->format('Y-m-d'),
        ]);
    }
}
