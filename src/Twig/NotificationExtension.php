<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\NotificationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class NotificationExtension extends AbstractExtension
{
    public function __construct(
        private readonly Security $security,
        private readonly NotificationRepository $notificationRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'gm_notifications',
                $this->getNotifications(...),
            ),
            new TwigFunction(
                'gm_unseen_notification_count',
                $this->getUnreadCount(...),
            ),
        ];
    }

    public function getNotifications(): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return [];
        }

        return $this->notificationRepository
            ->findUnreadByGm($user);
    }

    public function getUnreadCount(): int
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return 0;
        }

        return $this->notificationRepository
            ->countUnseenByGm($user);
    }
}
