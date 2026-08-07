<?php

namespace App\Controller;

use App\Repository\CampaignRepository;
use App\Repository\GameSessionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends BaseController
{
    public function __construct(
        private readonly CampaignRepository $campaignRepository,
        private readonly GameSessionRepository $gameSessionRepository,
    ) {
    }

    #[Route('/', name: 'dashboard', methods: ['GET'])]
    public function index(): Response
    {
        $campaigns = $this->campaignRepository->findActiveByOwner(
            $this->getCurrentUser(),
        );

        $upcomingSessions = $this->gameSessionRepository
            ->findUpcomingByOwner(
                $this->getCurrentUser(),
            );

        return $this->render('dashboard/index.html.twig', [
            'campaigns' => $campaigns,
            'nextSession' => $upcomingSessions[0] ?? null,
            'upcomingSessions' => array_slice($upcomingSessions, 1, 4),
        ]);
    }
}
