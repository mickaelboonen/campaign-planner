<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use App\Service\Notification\EmailParticipantAccessNotifier;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ParticipantRecoveryController extends BaseController
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
        TranslatorInterface $translator,
        #[Target('participant_recovery_ip')]
        RateLimiterFactoryInterface $participantRecoveryIpLimiter,
        #[Target('participant_recovery_email')]
        RateLimiterFactoryInterface $participantRecoveryEmailLimiter,
    ): Response {
        if ($request->isMethod('POST')) {
            $this->denyInvalidCsrf(
                'participant-recover',
                $request->request->get('_token'),
                $translator,
            );

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

            if (!$ipLimit->isAccepted() || !$emailLimit->isAccepted()) {
                $this->addFlash('error', 'recovery.rate_limited');

                throw new TooManyRequestsHttpException(
                    null,
                    $translator->trans(
                        'controller.recovery.rate_limited',
                        domain: 'error',
                    ),
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

            $this->addFlash('success', 'recovery.sent');

            return $this->redirectToRoute('participant_recover');
        }

        return $this->render(
            'participant_access/recover.html.twig',
        );
    }
}
