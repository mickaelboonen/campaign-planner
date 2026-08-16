<?php

namespace App\Controller;

use App\DTO\CreateCampaignData;
use App\DTO\EditCampaignData;
use App\DTO\UpdateCampaignNotesData;
use App\Entity\Campaign;
use App\Entity\GameSession;
use App\Form\CampaignNotesType;
use App\Form\CampaignType;
use App\Repository\CampaignRepository;
use App\Repository\GameSessionRepository;
use App\Repository\ParticipantRepository;
use App\Security\Voter\CampaignVoter;
use App\Service\CampaignManager;
use App\Service\Notification\SessionNotificationManager;
use App\Service\SessionManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        return $this->render('campaign/list.html.twig', [
            'campaigns' => $this->campaignRepository
                ->findActiveByOwner($this->getCurrentUser()),
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
                $data,
            );

            $this->addFlash('success', 'campaign.created');

            return $this->redirectToRoute('campaign_list');
        }

        return $this->render('campaign/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Campaign $campaign): Response
    {
        $this->denyAccessUnlessGranted(CampaignVoter::VIEW, $campaign);

        return $this->render('campaign/show.html.twig', [
            'campaign' => $campaign,
            'participants' => $this->participantRepository
                ->findActiveByCampaign($campaign),
            'upcomingSessions' => $this->gameSessionRepository
                ->findUpcomingByCampaign($campaign),
            'pastSessions' => $this->gameSessionRepository
                ->findPastByCampaign($campaign),
            'archivedParticipants' => $this->participantRepository
                ->findArchivedByCampaign($campaign),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Campaign $campaign,
        Request $request,
    ): Response {
        $this->denyAccessUnlessGranted(CampaignVoter::EDIT, $campaign);

        $data = new EditCampaignData();
        $data->name = $campaign->getName();
        $data->color = $campaign->getColor();

        $form = $this->createForm(
            CampaignType::class,
            $data,
            ['data_class' => EditCampaignData::class],
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->campaignManager->update($campaign, $data);
            $this->addFlash('success', 'campaign.updated');

            return $this->redirectToRoute('campaign_show', [
                'id' => $campaign->getId(),
            ]);
        }

        return $this->render('campaign/edit.html.twig', [
            'campaign' => $campaign,
            'form' => $form,
        ]);
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

        $this->denyInvalidCsrf(
            'cancel-session-'.$session->getId(),
            $request->request->get('_token'),
            $translator,
        );

        try {
            $sessionManager->cancel($session);
            $sessionNotificationManager->notifySessionCancelled($session);
            $this->addFlash('success', 'session.cancelled');
        } catch (\DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('campaign_show', [
            'id' => $campaign->getId(),
        ]);
    }

    #[Route('/{id}/archive', name: 'archive', methods: ['POST'])]
    public function archive(
        Campaign $campaign,
        Request $request,
        TranslatorInterface $translator,
    ): Response {
        $this->denyAccessUnlessGranted(CampaignVoter::EDIT, $campaign);

        $this->denyInvalidCsrf(
            'archive-campaign-'.$campaign->getId(),
            $request->request->get('_token'),
            $translator,
        );

        try {
            $this->campaignManager->archive($campaign);
            $this->addFlash('success', 'campaign.archived');
        } catch (\DomainException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('campaign_list');
    }

    #[Route('/{id}/notes', name: 'notes', methods: ['GET', 'POST'])]
    public function notes(
        Campaign $campaign,
        Request $request,
    ): Response {
        $this->denyAccessUnlessGranted(CampaignVoter::EDIT, $campaign);

        $data = new UpdateCampaignNotesData();
        $data->privateNotes = $campaign->getPrivateNotes();

        $form = $this->createForm(CampaignNotesType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->campaignManager->updateNotes($campaign, $data);
            $this->addFlash('success', 'campaign.notes_saved');

            return $this->redirectToRoute('campaign_notes', [
                'id' => $campaign->getId(),
            ]);
        }

        return $this->render('campaign/notes.html.twig', [
            'campaign' => $campaign,
            'form' => $form,
        ]);
    }
}
