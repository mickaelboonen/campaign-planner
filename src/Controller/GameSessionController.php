<?php

namespace App\Controller;

use App\DTO\GameSessionData;
use App\Entity\CalendarSlot;
use App\Entity\Campaign;
use App\Entity\GameSession;
use App\Form\GameSessionType;
use App\Security\Voter\CampaignVoter;
use App\Service\Notification\SessionNotificationManager;
use App\Service\SessionManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/campaign/{id}/session', name: 'campaign_session_')]
final class GameSessionController extends BaseController
{
    #[Route('/new/{slot}', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Campaign $campaign,
        CalendarSlot $slot,
        Request $request,
        SessionManager $sessionManager,
        SessionNotificationManager $notificationManager,
        TranslatorInterface $translator,
    ): Response {
        $this->denyAccessUnlessGranted(CampaignVoter::EDIT, $campaign);

        if ($slot->getCampaign() !== $campaign) {
            throw $this->createNotFoundException(
                $translator->trans(
                    'controller.session.slot_wrong_campaign',
                    domain: 'error',
                ),
            );
        }

        $data = new GameSessionData();
        $form = $this->createForm(GameSessionType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $session = $sessionManager->scheduleFromSlot(
                    $slot,
                    $data->name,
                    $data->startTime,
                    $data->endTime,
                );

                $notificationManager->notifySessionScheduled($session);
                $this->addFlash('success', 'session.scheduled');

                return $this->redirectToRoute('campaign_show', [
                    'id' => $campaign->getId(),
                ]);
            } catch (\DomainException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->render('game_session/new.html.twig', [
            'campaign' => $campaign,
            'slot' => $slot,
            'form' => $form,
        ]);
    }

    #[Route('/{session}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Campaign $campaign,
        GameSession $session,
        Request $request,
        SessionManager $sessionManager,
        TranslatorInterface $translator,
    ): Response {
        $this->denyAccessUnlessGranted(CampaignVoter::EDIT, $campaign);

        if ($session->getCampaign() !== $campaign) {
            throw $this->createNotFoundException(
                $translator->trans(
                    'controller.session.wrong_campaign',
                    domain: 'error',
                ),
            );
        }

        $data = new GameSessionData();
        $data->name = $session->getName();
        $data->startTime = $session->getStartTime();
        $data->endTime = $session->getEndTime();

        $form = $this->createForm(GameSessionType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sessionManager->update(
                $session,
                $data->name,
                $data->startTime,
                $data->endTime,
            );

            $this->addFlash('success', 'session.updated');

            return $this->redirectToRoute('campaign_show', [
                'id' => $campaign->getId(),
            ]);
        }

        return $this->render('game_session/edit.html.twig', [
            'campaign' => $campaign,
            'session' => $session,
            'form' => $form,
        ]);
    }
}
