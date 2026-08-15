<?php

namespace App\Service\Notification;

use App\Entity\GameSession;
use App\Entity\Participant;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use App\Repository\ParticipantRepository;

final readonly class EmailSessionNotifier implements SessionNotifierInterface
{
    private const SENDER_EMAIL = 'campaign-planner@alwaysdata.net';
    private const SENDER_NAME = 'CampaignPlanner';

    public function __construct(
        private MailerInterface $mailer,
        private RouterInterface $router,
        private ParticipantRepository $participantRepository,
    ) {
    }

    public function notify(GameSession $session): void
    {
        foreach ($this->getActiveParticipants($session) as $participant) {
            if (!$participant->getEmail()) {
                continue;
            }

            $email = (new TemplatedEmail())
                ->from($this->getSender())
                ->to($participant->getEmail())
                ->subject(
                    $this->setMailSubject(
                        'Nouvelle session planifiée',
                        $session,
                    ),
                )
                ->htmlTemplate('emails/session_scheduled.html.twig')
                ->context(
                    $this->getContext($session, $participant),
                );

            $this->mailer->send($email);
        }
    }

    public function notifyCancellation(GameSession $session): void
    {
        foreach ($this->getActiveParticipants($session) as $participant) {
            if (!$participant->getEmail()) {
                continue;
            }

            $email = (new TemplatedEmail())
                ->from($this->getSender())
                ->to($participant->getEmail())
                ->subject(
                    $this->setMailSubject(
                        'Session annulée',
                        $session,
                    ),
                )
                ->htmlTemplate('emails/session_cancelled.html.twig')
                ->context(
                    $this->getContext($session, $participant),
                );

            $this->mailer->send($email);
        }
    }

    public function notifyReminder(GameSession $session): void
    {
        foreach ($this->getActiveParticipants($session) as $participant) {
            if (!$participant->getEmail()) {
                continue;
            }

            $email = (new TemplatedEmail())
                ->from($this->getSender())
                ->to($participant->getEmail())
                ->subject(
                    $this->setMailSubject(
                        'Rappel — Session à venir',
                        $session,
                    ),
                )
                ->htmlTemplate('emails/session_reminder.html.twig')
                ->context(
                    $this->getContext($session, $participant),
                );

            $this->mailer->send($email);
        }
    }

    private function getContext(
        GameSession $session,
        Participant $participant,
    ): array {
        return [
            'participant' => $participant,
            'campaign' => $session->getCampaign(),
            'session' => $session,
            'dashboardUrl' => $this->getDashboardUrl($participant),
        ];
    }

    private function getDashboardUrl(Participant $participant): string
    {
        return $this->router->generate(
            'participant_dashboard',
            [
                'token' => $participant->getDashboardToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }

    private function setMailSubject(
        string $type,
        GameSession $session,
    ): string {
        return sprintf(
            '%s — %s',
            $type,
            $session->getCampaign()->getName(),
        );
    }

    private function getSender(): Address
    {
        return new Address(
            self::SENDER_EMAIL,
            self::SENDER_NAME,
        );
    }

    private function getActiveParticipants(
        GameSession $session,
    ): array {
        return $this->participantRepository->findActiveByCampaign(
            $session->getCampaign(),
        );
    }
}
