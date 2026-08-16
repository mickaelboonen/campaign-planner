<?php

namespace App\Service\Notification;

use App\Entity\GameSession;
use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class EmailSessionNotifier implements SessionNotifierInterface
{
    private const SENDER_EMAIL = 'campaign-planner@alwaysdata.net';
    private const SENDER_NAME = 'CampaignPlanner';

    public function __construct(
        private MailerInterface $mailer,
        private RouterInterface $router,
        private ParticipantRepository $participantRepository,
        private TranslatorInterface $translator,
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
                    $this->getSubject(
                        'session_scheduled.subject',
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
                    $this->getSubject(
                        'session_cancelled.subject',
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
                    $this->getSubject(
                        'session_reminder.subject',
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

    private function getSubject(
        string $key,
        GameSession $session,
    ): string {
        return $this->translator->trans(
            $key,
            [
                '%campaign%' => $session
                    ->getCampaign()
                    ->getName(),
            ],
            'emails',
        );
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
            self::SENDER_EMAIL,
            self::SENDER_NAME,
        );
    }

    private function getActiveParticipants(
        GameSession $session,
    ): array {
        return $this->participantRepository
            ->findActiveByCampaign(
                $session->getCampaign(),
            );
    }
}
