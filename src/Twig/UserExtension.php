<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserExtension extends AbstractExtension
{
    public function __construct(
        private readonly Security $security,
        private readonly ParticipantRepository $participantRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'player_dashboard_url',
                $this->getPlayerDashboardUrl(...),
            ),
        ];
    }

    public function getPlayerDashboardUrl(): ?string
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return null;
        }

        $participant = $this->participantRepository
            ->findOneActiveByEmail($user->getEmail());

        if ($participant === null) {
            return null;
        }

        return $this->urlGenerator->generate(
            'participant_dashboard',
            [
                'token' => $participant->getDashboardToken(),
            ],
        );
    }
}
