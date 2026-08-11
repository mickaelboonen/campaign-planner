<?php

namespace App\Service\Notification;

use App\Entity\Feedback;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final readonly class EmailFeedbackNotifier
{
    private const FROM_EMAIL = 'campaign-planner@alwaysdata.net';
    private const FROM_NAME = 'CampaignPlanner';

    public function __construct(
        private MailerInterface $mailer,
        private string $adminEmail,
    ) {
    }

    public function notify(Feedback $feedback): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address(
                self::FROM_EMAIL,
                self::FROM_NAME,
            ))
            ->to($this->adminEmail)
            ->subject(sprintf(
                '[%s] Nouveau message CampaignPlanner',
                $feedback->getType()->label(),
            ))
            ->htmlTemplate(
                'emails/feedback_received.html.twig',
            )
            ->context([
                'feedback' => $feedback,
            ]);

        $this->mailer->send($email);
    }
}
