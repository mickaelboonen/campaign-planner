<?php

namespace App\Controller;

use App\DTO\GameSessionData;
use App\Entity\Campaign;
use App\Entity\CalendarSlot;
use App\Entity\GameSession;
use App\Form\GameSessionType;
use App\Security\Voter\CampaignVoter;
use App\Service\SessionManager;
use App\Service\Notification\SessionNotificationManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
    ): Response {
        $this->denyAccessUnlessGranted(CampaignVoter::EDIT, $campaign);

        if ($slot->getCampaign() !== $campaign) {
            throw $this->createNotFoundException(
                'Ce créneau n’appartient pas à cette campagne.',
            );
        }

        $data = new GameSessionData();
        $form = $this->createForm(GameSessionType::class, $data);
        $form->handleRequest($request);
dump([
    'method' => $request->getMethod(),
    'form_name' => $form->getName(),
    'request_keys' => array_keys($request->request->all()),
    'request_data' => $request->request->all(),
]);
    dump($form->getErrors(true, true));
    dump($form->isSubmitted() );
    dump($form->isSubmitted() && $form->isValid());

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $session = $sessionManager->scheduleFromSlot(
                    $slot,
                    $data->name,
                    $data->startTime,
                    $data->endTime,
                );

                $notificationManager->notifySessionScheduled($session);

                $this->addFlash(
                    'success',
                    'La session a bien été planifiée.',
                );

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
    ): Response {
        $this->denyAccessUnlessGranted(CampaignVoter::EDIT, $campaign);

        if ($session->getCampaign() !== $campaign) {
            throw $this->createNotFoundException(
                'Cette session n’appartient pas à cette campagne.',
            );
        }

        $data = new GameSessionData();
        $data->name = $session->getName();
        $data->startTime = $session->getStartTime();
        $data->endTime = $session->getEndTime();

        $form = $this->createForm(GameSessionType::class, $data);
        $form->handleRequest($request);

    dump($form->getErrors(true, true));
        if ($form->isSubmitted() && $form->isValid()) {
            $sessionManager->update(
                $session,
                $data->name,
                $data->startTime,
                $data->endTime,
            );

            $this->addFlash(
                'success',
                'Les horaires de la session ont bien été modifiés.',
            );

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
