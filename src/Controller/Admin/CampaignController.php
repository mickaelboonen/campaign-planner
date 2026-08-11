<?php

namespace App\Controller\Admin;

use App\Entity\Campaign;
use App\Repository\CampaignRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/campaigns', name: 'admin_campaign_')]
#[IsGranted('ROLE_ADMIN')]
final class CampaignController extends AbstractController
{
    public function __construct(
        private readonly CampaignRepository $campaignRepository,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $status = (string) $request->query->get('status', 'active');
        $search = trim((string) $request->query->get('q', ''));

        if (!in_array($status, ['active', 'archived', 'all'], true)) {
            $status = 'active';
        }

        $campaigns = $this->paginator->paginate(
            $this->campaignRepository->createAdminQuery($status, $search),
            max(1, $request->query->getInt('page', 1)),
            20,
        );

        return $this->render('admin/campaign/list.html.twig', [
            'campaigns' => $campaigns,
            'currentStatus' => $status,
            'search' => $search,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Campaign $campaign): Response
    {
        return $this->render('admin/campaign/show.html.twig', [
            'campaign' => $campaign,
        ]);
    }
}
