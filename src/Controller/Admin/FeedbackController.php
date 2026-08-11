<?php

namespace App\Controller\Admin;

use App\Entity\Feedback;
use App\Repository\FeedbackRepository;
use App\Service\FeedbackManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/admin/feedbacks', name: 'admin_feedback_')]
#[IsGranted('ROLE_ADMIN')]
final class FeedbackController extends AbstractController
{
    public function __construct(
        private readonly FeedbackRepository $feedbackRepository,
        private readonly FeedbackManager $feedbackManager,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $status = (string) $request->query->get('status', 'open');

        if (!in_array($status, ['open', 'new', 'read', 'closed', 'all'], true)) {
            $status = 'open';
        }

        $feedbacks = $this->paginator->paginate(
            $this->feedbackRepository->createAdminQuery($status),
            max(1, $request->query->getInt('page', 1)),
            20,
        );

        return $this->render('admin/feedback/list.html.twig', [
            'feedbacks' => $feedbacks,
            'currentStatus' => $status,
            'counts' => $this->feedbackRepository->countByStatus(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(
        Feedback $feedback,
    ): Response {
        $this->feedbackManager->markAsRead($feedback);

        return $this->render(
            'admin/feedback/show.html.twig',
            [
                'feedback' => $feedback,
            ],
        );
    }

    #[Route(
        '/{id}/close',
        name: 'close',
        methods: ['POST'],
    )]
    public function close(
        Feedback $feedback,
        Request $request,
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid(
            'close-feedback-'.$feedback->getId(),
            (string) $request->request->get('_token'),
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.',
            );
        }

        $this->feedbackManager->close($feedback);

        $this->addFlash(
            'success',
            'Le feedback a bien été clôturé.',
        );

        return $this->redirectToRoute(
            'admin_feedback_show',
            [
                'id' => $feedback->getId(),
            ],
        );
    }

    #[Route(
        '/{id}/reopen',
        name: 'reopen',
        methods: ['POST'],
    )]
    public function reopen(
        Feedback $feedback,
        Request $request,
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid(
            'reopen-feedback-'.$feedback->getId(),
            (string) $request->request->get('_token'),
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.',
            );
        }

        $this->feedbackManager->reopen($feedback);

        $this->addFlash(
            'success',
            'Le feedback a bien été rouvert.',
        );

        return $this->redirectToRoute(
            'admin_feedback_show',
            [
                'id' => $feedback->getId(),
            ],
        );
    }
}
