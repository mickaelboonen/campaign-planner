<?php

namespace App\Service\Notification;

use App\Entity\Participant;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class EmailParticipantAccessNotifier
{
    private const FROM_EMAIL = 'campaign-planner@alwaysdata.net';
    private const FROM_NAME = 'CampaignPlanner';

    public function __construct(
        private MailerInterface $mailer,
        private RouterInterface $router,
        private TranslatorInterface $translator,
    ) {
    }

    public function notifyInvitation(
        Participant $participant,
    ): void {
        if (!$participant->getEmail()) {
            return;
        }

        $campaign = $participant->getCampaign();

        $email = (new TemplatedEmail())
            ->from($this->getSender())
            ->to($participant->getEmail())
            ->subject(
                $this->translator->trans(
                    'participant_access.subject',
                    ['%campaign%' => $campaign->getName()],
                    'emails',
                ),
            )
            ->htmlTemplate('emails/participant_access.html.twig')
            ->context([
                'participant' => $participant,
                'campaign' => $campaign,
                'dashboardUrl' => $this->getDashboardUrl($participant),
            ]);

        $this->mailer->send($email);
    }

    public function notifyRecovery(
        Participant $participant,
    ): void {
        if (!$participant->getEmail()) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from($this->getSender())
            ->to($participant->getEmail())
            ->subject(
                $this->translator->trans(
                    'participant_access_recovery.subject',
                    domain: 'emails',
                ),
            )
            ->htmlTemplate(
                'emails/participant_access_recovery.html.twig',
            )
            ->context([
                'participant' => $participant,
                'dashboardUrl' => $this->getDashboardUrl($participant),
            ]);

        $this->mailer->send($email);
    }

    private function getDashboardUrl(
        Participant $participant,
    ): string {
        return $this->router->generate(
            'participant_dashboard',
            [
                'token' => $participant->getDashboardToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    private function getSender(): Address
    {
        return new Address(
            self::FROM_EMAIL,
            self::FROM_NAME,
        );
    }
}
