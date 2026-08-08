<?php

namespace App\Service\Notification;

use App\Entity\GameSession;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

final readonly class EmailSessionNotifier implements SessionNotifierInterface
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function notify(GameSession $session): void
    {
        foreach ($session->getCampaign()->getParticipants() as $participant) {
            if (!$participant->getEmail()) {
                continue;
            }

            $email = (new TemplatedEmail())
                ->from('noreply@campaign-planner.local')
                ->to($participant->getEmail())
                ->subject(sprintf(
                    'Nouvelle session planifiée — %s',
                    $session->getCampaign()->getName(),
                ))
                ->htmlTemplate(
                    'emails/session_scheduled.html.twig',
                )
                ->context([
                    'participant' => $participant,
                    'campaign' => $session->getCampaign(),
                    'session' => $session,
                ]);

            $this->mailer->send($email);
        }
    }

    public function notifyCancellation(
        GameSession $session,
    ): void {
        foreach ($session->getCampaign()->getParticipants() as $participant) {
            if (!$participant->getEmail()) {
                continue;
            }

            $email = (new TemplatedEmail())
                ->from('noreply@campaign-planner.local')
                ->to($participant->getEmail())
                ->subject(sprintf(
                    'Session annulée — %s',
                    $session->getCampaign()->getName(),
                ))
                ->htmlTemplate(
                    'emails/session_cancelled.html.twig',
                )
                ->context([
                    'participant' => $participant,
                    'campaign' => $session->getCampaign(),
                    'session' => $session,
                ]);

            $this->mailer->send($email);
        }
    }
}
