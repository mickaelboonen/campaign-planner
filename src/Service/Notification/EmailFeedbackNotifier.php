<?php

namespace App\Service\Notification;

use App\Entity\Feedback;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class EmailFeedbackNotifier
{
    private const FROM_EMAIL = 'campaign-planner@alwaysdata.net';
    private const FROM_NAME = 'CampaignPlanner';

    public function __construct(
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private string $adminEmail,
    ) {
    }

    public function notify(Feedback $feedback): void
    {
        $locale = $this->translator->getLocale();

        $type = $this->translator->trans(
            $feedback->getType()->translationKey(),
            domain: 'enums',
            locale: $locale,
        );

        $subject = $this->translator->trans(
            'feedback_received.subject',
            ['%type%' => $type],
            'emails',
            $locale,
        );

        $email = (new TemplatedEmail())
            ->from(new Address(
                self::FROM_EMAIL,
                self::FROM_NAME,
            ))
            ->to($this->adminEmail)
            ->subject($subject)
            ->htmlTemplate('emails/feedback_received.html.twig')
            ->locale($locale)
            ->context([
                'feedback' => $feedback,
            ]);

        $this->mailer->send($email);
    }
}
