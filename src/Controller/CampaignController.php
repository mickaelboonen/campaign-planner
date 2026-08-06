<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Repository\ParticipantRepository;
use App\Security\Voter\CampaignVoter;
use App\DTO\CreateCampaignData;
use App\Form\CampaignType;
use App\Repository\CampaignRepository;
use App\Service\CampaignManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/campaign', name: 'campaign_')]
final class CampaignController extends BaseController
{
    public function __construct(
        private readonly CampaignRepository $campaignRepository,
        private readonly ParticipantRepository $participantRepository,
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

        return $this->render('campaign/show.html.twig', [
            'campaign' => $campaign,
            'participants' => $participants,
        ]);
    }
}