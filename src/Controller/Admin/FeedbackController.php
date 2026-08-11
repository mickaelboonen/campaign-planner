<?php

namespace App\Controller\Admin;

use App\Repository\FeedbackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/feedbacks', name: 'admin_feedback_')]
final class FeedbackController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        FeedbackRepository $feedbackRepository,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render(
            'admin/feedback/list.html.twig',
            [
                'feedbacks' => $feedbackRepository->findForAdmin(),
            ],
        );
    }
}
