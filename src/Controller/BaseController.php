<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Campaign;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class BaseController extends AbstractController
{
    protected function getCurrentUser(): User
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException(
                'No authenticated user found.'
            );
        }

        return $user;
    }

    protected function denyArchivedCampaign(
        Campaign $campaign,
        string $message = 'Cette campagne n’est plus active.',
    ): void {
        if ($campaign->isArchived()) {
            throw $this->createNotFoundException(
                $message,
            );
        }
    }
}
