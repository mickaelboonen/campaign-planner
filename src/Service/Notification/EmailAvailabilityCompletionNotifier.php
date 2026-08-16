<?php

namespace App\Service\Notification;

use App\Entity\Campaign;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class EmailAvailabilityCompletionNotifier
{
    private const FROM_EMAIL = 'campaign-planner@alwaysdata.net';
    private const FROM_NAME = 'CampaignPlanner';

    public function __construct(
        private MailerInterface $mailer,
        private RouterInterface $router,
        private TranslatorInterface $translator,
    ) {
    }

    public function notify(
        Campaign $campaign,
        \DateTimeImmutable $weekStart,
    ): void {
        $gm = $campaign->getOwner();

        if ($gm === null || !$gm->getEmail()) {
            return;
        }

        $weekEnd = $weekStart->modify('+6 days');

        $calendarUrl = $this->router->generate(
            'calendar_show',
            [
                'id' => $campaign->getId(),
                'week' => $weekStart->format('Y-m-d'),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $email = (new TemplatedEmail())
            ->from(new Address(
                self::FROM_EMAIL,
                self::FROM_NAME,
            ))
            ->to($gm->getEmail())
            ->subject(
                $this->translator->trans(
                    'availability_complete.subject',
                    ['%campaign%' => $campaign->getName()],
                    'emails',
                ),
            )
            ->htmlTemplate('emails/availability_complete.html.twig')
            ->context([
                'campaign' => $campaign,
                'weekStart' => $weekStart,
                'weekEnd' => $weekEnd,
                'calendarUrl' => $calendarUrl,
            ]);

        $this->mailer->send($email);
    }
}
