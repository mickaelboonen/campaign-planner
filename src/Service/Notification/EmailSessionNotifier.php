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
                ->subject($this->setMailSubject('Nouvelle session planifiée', $session))
                ->htmlTemplate('emails/session_scheduled.html.twig')
                ->context([
                    'participant' => $participant,
                    'campaign' => $session->getCampaign(),
                    'session' => $session,
                ]);

            $this->mailer->send($email);
        }
    }

    public function notifyCancellation(GameSession $session): void
    {
        foreach ($session->getCampaign()->getParticipants() as $participant) {
            if (!$participant->getEmail()) {
                continue;
            }

            $email = (new TemplatedEmail())
                ->from('noreply@campaign-planner.local')
                ->to($participant->getEmail())
                ->subject($this->setMailSubject('Session annulée', $session))
                ->htmlTemplate('emails/session_cancelled.html.twig')
                ->context([
                    'participant' => $participant,
                    'campaign' => $session->getCampaign(),
                    'session' => $session,
                ]);

            $this->mailer->send($email);
        }
    }

    public function notifyReminder(GameSession $session): void
    {
        foreach ($session->getCampaign()->getParticipants() as $participant) {
            if (!$participant->getEmail()) {
                continue;
            }

            $email = (new TemplatedEmail())
                ->from('noreply@campaign-planner.local')
                ->to($participant->getEmail())
                ->subject($this->setMailSubject('Rappel — Session à venir', $session))
                ->htmlTemplate('emails/session_reminder.html.twig')
                ->context([
                    'participant' => $participant,
                    'campaign' => $session->getCampaign(),
                    'session' => $session,
                ]);

            $this->mailer->send($email);
        }
    }

     private function setMailSubject(string $type, GameSession $session): string
     {
        return sprintf('%s — %s', $type, $session->getCampaign()->getName());
     }
}
