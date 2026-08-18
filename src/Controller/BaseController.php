<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Entity\User;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class BaseController extends AbstractController
{
    protected function getCurrentUser(): User
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }

    protected function denyArchivedCampaign(
        Campaign $campaign,
        string $message,
    ): void {
        if ($campaign->isArchived()) {
            throw $this->createNotFoundException($message);
        }
    }
    protected function denyInvalidCsrf(
        string $tokenId,
        mixed $token,
        TranslatorInterface $translator,
    ): void {
        if ($this->isCsrfTokenValid($tokenId, (string) $token)) {
            return;
        }

        throw $this->createAccessDeniedException(
            $translator->trans(
                'controller.security.invalid_csrf',
                domain: 'error',
            ),
        );
    }
}
