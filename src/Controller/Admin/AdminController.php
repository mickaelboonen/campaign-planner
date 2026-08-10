<?php

namespace App\Controller\Admin;

use App\Repository\CampaignRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('', name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(
        UserRepository $userRepository,
        CampaignRepository $campaignRepository,
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'userCount' => $userRepository->count(),
            'campaignCount' => $campaignRepository->count(),
        ]);
    }
}
