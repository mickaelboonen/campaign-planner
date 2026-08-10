<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use App\Service\Notification\EmailParticipantAccessNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ParticipantRecoveryController extends AbstractController
{
    #[Route(
        '/player/recover',
        name: 'participant_recover',
        methods: ['GET', 'POST'],
    )]
    public function recover(
        Request $request,
        ParticipantRepository $participantRepository,
        EmailParticipantAccessNotifier $emailParticipantAccessNotifier,
        #[Target('participant_recovery_ip')]
        RateLimiterFactoryInterface $participantRecoveryIpLimiter,
        #[Target('participant_recovery_email')]
        RateLimiterFactoryInterface $participantRecoveryEmailLimiter,
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid(
                'participant-recover',
                (string) $request->request->get('_token'),
            )) {
                throw $this->createAccessDeniedException(
                    'Jeton CSRF invalide.',
                );
            }

            $email = trim(
                mb_strtolower(
                    (string) $request->request->get('email'),
                ),
            );

            $ipLimit = $participantRecoveryIpLimiter
                ->create($request->getClientIp() ?? 'unknown')
                ->consume();

            $emailLimit = $participantRecoveryEmailLimiter
                ->create(hash('sha256', $email))
                ->consume();

            if (
                !$ipLimit->isAccepted()
                || !$emailLimit->isAccepted()
            ) {
                $this->addFlash(
                    'error',
                    'Trop de demandes ont été effectuées. Veuillez patienter avant de demander un nouveau lien.',
                );
                throw new TooManyRequestsHttpException(
                    null,
                    'Trop de demandes ont été effectuées. Veuillez réessayer plus tard.',
                );
            }

            if ($email !== '') {
                $participant = $participantRepository
                    ->findOneActiveByEmail($email);

                if ($participant !== null) {
                    $emailParticipantAccessNotifier
                        ->notifyRecovery($participant);
                }
            }

            $this->addFlash(
                'success',
                'Si cette adresse est associée à un joueur CampaignPlanner, un lien d’accès vient de lui être envoyé.',
            );

            return $this->redirectToRoute(
                'participant_recover',
            );
        }

        return $this->render(
            'participant_access/recover.html.twig',
        );
    }
}
