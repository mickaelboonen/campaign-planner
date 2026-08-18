<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class NotificationController extends BaseController
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(
        '/notifications/{id}/open',
        name: 'notification_open',
        methods: ['POST'],
    )]
    public function open(
        Notification $notification,
        Request $request,
    ): RedirectResponse {
        if ($notification->getGm() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $this->denyInvalidCsrf(
            'notification_open_'.$notification->getId(),
            $request->request->get('_token'),
            $this->translator
        );

        $notification->markAsRead();
        $this->entityManager->flush();

        return $this->redirectToRoute('calendar_show', [
            'id' => $notification->getCampaign()->getId(),
            'week' => $notification->getWeekStart()->format('Y-m-d'),
        ]);
    }

    #[Route(
        '/notifications/mark-seen',
        name: 'notifications_mark_seen',
        methods: ['POST'],
    )]
    public function markSeen(): Response
    {
        $user = $this->getCurrentUser();

        foreach (
            $this->notificationRepository->findUnreadByGm($user)
            as $notification
        ) {
            $notification->markAsSeen();
        }

        $this->entityManager->flush();

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route(
        '/notifications/mark-all-read',
        name: 'notifications_mark_all_read',
        methods: ['POST'],
    )]
    public function markAllRead(
        Request $request,
    ): RedirectResponse {
        $user = $this->getCurrentUser();

        $this->denyInvalidCsrf(
            'notifications_mark_all_read',
            $request->request->get('_token'),
            $this->translator
        );

        foreach (
            $this->notificationRepository->findUnreadByGm($user)
            as $notification
        ) {
            $notification->markAsRead();
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('dashboard');
    }
}
