<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Repository\ParticipantRepository;
use App\Security\Voter\CampaignVoter;
use App\DTO\CreateCampaignData;
use App\DTO\EditCampaignData;
use App\Form\CampaignType;
use App\Repository\GameSessionRepository;
use App\Repository\CampaignRepository;
use App\Service\CampaignManager;
use App\Entity\GameSession;
use App\Service\SessionManager;
use App\Service\Notification\SessionNotificationManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/campaign', name: 'campaign_')]
final class CampaignController extends BaseController
{
    public function __construct(
        private readonly CampaignRepository $campaignRepository,
        private readonly ParticipantRepository $participantRepository,
        private readonly GameSessionRepository $gameSessionRepository,
        private readonly CampaignManager $campaignManager,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): Response
    {
        $campaigns = $this->campaignRepository->findActiveByOwner(
            $this->getCurrentUser()
        );

        return $this->render('campaign/list.html.twig', [
            'campaigns' => $campaigns,
        ]);
    }

    #[Route('/new', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $data = new CreateCampaignData();

        $form = $this->createForm(CampaignType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->campaignManager->create(
                $this->getCurrentUser(),
                $data
            );

            $this->addFlash(
                'success',
                'La campagne a bien été créée.'
            );

            return $this->redirectToRoute('campaign_list');
        }

        return $this->render('campaign/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Campaign $campaign): Response
    {
        $this->denyAccessUnlessGranted(
            CampaignVoter::VIEW,
            $campaign,
        );

        $participants = $this->participantRepository
            ->findActiveByCampaign($campaign);

        $upcomingSessions = $this->gameSessionRepository
            ->findUpcomingByCampaign($campaign);

        $pastSessions = $this->gameSessionRepository
            ->findPastByCampaign($campaign);

        return $this->render('campaign/show.html.twig', [
            'campaign' => $campaign,
            'participants' => $participants,
            'upcomingSessions' => $upcomingSessions,
            'pastSessions' => $pastSessions,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Campaign $campaign,
        Request $request,
    ): Response {
        $this->denyAccessUnlessGranted(
            CampaignVoter::EDIT,
            $campaign,
        );

        $data = new EditCampaignData();
        $data->name = $campaign->getName();
        $data->color = $campaign->getColor();

        $form = $this->createForm(
            CampaignType::class,
            $data,
            [
                'data_class' => EditCampaignData::class,
            ],
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->campaignManager->update(
                $campaign,
                $data,
            );

            $this->addFlash(
                'success',
                'La campagne a bien été modifiée.',
            );

            return $this->redirectToRoute(
                'campaign_show',
                [
                    'id' => $campaign->getId(),
                ],
            );
        }

        return $this->render(
            'campaign/edit.html.twig',
            [
                'campaign' => $campaign,
                'form' => $form,
            ],
        );
    }

    #[Route(
        '/{id}/session/{session}/cancel',
        name: 'session_cancel',
        methods: ['POST'],
    )]
    public function cancelSession(
        Campaign $campaign,
        GameSession $session,
        Request $request,
        SessionManager $sessionManager,
        SessionNotificationManager $sessionNotificationManager,
    ): Response {
        $this->denyAccessUnlessGranted(
            CampaignVoter::EDIT,
            $campaign,
        );

        if ($session->getCampaign() !== $campaign) {
            throw $this->createNotFoundException(
                'Cette session n’appartient pas à cette campagne.',
            );
        }

        if (!$this->isCsrfTokenValid(
            'cancel-session-' . $session->getId(),
            (string) $request->request->get('_token'),
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.',
            );
        }

        try {
            $sessionManager->cancel($session);

            $sessionNotificationManager->notifySessionCancelled(
                $session,
            );

            $this->addFlash(
                'success',
                'La session a bien été annulée.',
            );
        } catch (\DomainException $exception) {
            $this->addFlash(
                'error',
                $exception->getMessage(),
            );
        }

        return $this->redirectToRoute(
            'campaign_show',
            [
                'id' => $campaign->getId(),
            ],
        );
    }

    #[Route(
        '/{id}/archive',
        name: 'archive',
        methods: ['POST'],
    )]
    public function archive(
        Campaign $campaign,
        Request $request,
    ): Response {
        $this->denyAccessUnlessGranted(
            CampaignVoter::EDIT,
            $campaign,
        );

        if (!$this->isCsrfTokenValid(
            'archive-campaign-' . $campaign->getId(),
            (string) $request->request->get('_token'),
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.',
            );
        }

        try {
            $this->campaignManager->archive($campaign);

            $this->addFlash(
                'success',
                'La campagne a bien été archivée.',
            );
        } catch (\DomainException $exception) {
            $this->addFlash(
                'error',
                $exception->getMessage(),
            );
        }

        return $this->redirectToRoute('campaign_list');
    }
}
