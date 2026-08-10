<?php

namespace App\Service\Notification;

use App\Entity\Participant;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final readonly class EmailParticipantAccessNotifier
{
    private const FROM_EMAIL = 'campaign-planner@alwaysdata.net';
    private const FROM_NAME = 'CampaignPlanner';

    public function __construct(
        private MailerInterface $mailer,
        private RouterInterface $router,
    ) {
    }

    public function notifyInvitation(
        Participant $participant,
    ): void {
        if (!$participant->getEmail()) {
            return;
        }

        $dashboardUrl = $this->getDashboardUrl($participant);

        $email = (new TemplatedEmail())
            ->from($this->getSender())
            ->to($participant->getEmail())
            ->subject(sprintf(
                'Vous avez été ajouté à %s',
                $participant->getCampaign()->getName(),
            ))
            ->htmlTemplate(
                'emails/participant_access.html.twig',
            )
            ->context([
                'participant' => $participant,
                'campaign' => $participant->getCampaign(),
                'dashboardUrl' => $dashboardUrl,
            ]);

        $this->mailer->send($email);
    }

    public function notifyRecovery(
        Participant $participant,
    ): void {
        if (!$participant->getEmail()) {
            return;
        }

        $dashboardUrl = $this->getDashboardUrl($participant);

        $email = (new TemplatedEmail())
            ->from($this->getSender())
            ->to($participant->getEmail())
            ->subject(
                'Votre lien d’accès CampaignPlanner',
            )
            ->htmlTemplate(
                'emails/participant_access_recovery.html.twig',
            )
            ->context([
                'participant' => $participant,
                'dashboardUrl' => $dashboardUrl,
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
