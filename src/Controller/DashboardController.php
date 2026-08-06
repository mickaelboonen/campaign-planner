<?php

namespace App\Controller;

use App\Repository\CampaignRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends BaseController
{
    public function __construct(
        private readonly CampaignRepository $campaignRepository,
    ) {
    }

    #[Route('/', name: 'dashboard', methods: ['GET'])]
    public function index(): Response
    {
        $campaigns = $this->campaignRepository->findActiveByOwner(
            $this->getCurrentUser(),
        );

        return $this->render('dashboard/index.html.twig', [
            'campaigns' => $campaigns,
        ]);
    }
}