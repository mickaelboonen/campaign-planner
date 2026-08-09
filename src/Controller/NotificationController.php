<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

final class NotificationController extends AbstractController
{
    #[Route(
        '/notifications/{id}/open',
        name: 'notification_open',
        methods: ['POST'],
    )]
    public function open(
        Notification $notification,
        Request $request,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        if ($notification->getGm() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'notification_open_'.$notification->getId(),
            $request->request->get('_token'),
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.',
            );
        }

        $notification->markAsRead();
        $entityManager->flush();

        return $this->redirectToRoute(
            'calendar_show',
            [
                'id' => $notification->getCampaign()->getId(),
                'week' => $notification
                    ->getWeekStart()
                    ->format('Y-m-d'),
            ],
        );
    }

    #[Route(
        '/notifications/mark-seen',
        name: 'notifications_mark_seen',
        methods: ['POST'],
    )]
    public function markSeen(
        NotificationRepository $notificationRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        foreach ($notificationRepository->findUnreadByGm($user) as $notification) {
            $notification->markAsSeen();
        }

        $entityManager->flush();

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route(
        '/notifications/mark-all-read',
        name: 'notifications_mark_all_read',
        methods: ['POST'],
    )]
    public function markAllRead(
        Request $request,
        NotificationRepository $notificationRepository,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'notifications_mark_all_read',
            $request->request->get('_token'),
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.',
            );
        }

        foreach ($notificationRepository->findUnreadByGm($user) as $notification) {
            $notification->markAsRead();
        }

        $entityManager->flush();

        return $this->redirectToRoute('dashboard');
    }
}
