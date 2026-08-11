<?php

namespace App\Service;

use App\DTO\CreateFeedbackData;
use App\Entity\Feedback;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final readonly class FeedbackManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(
        CreateFeedbackData $data,
        ?User $user = null,
        ?string $pageUrl = null,
    ): Feedback {
        $feedback = new Feedback();

        $feedback->setType($data->type);
        $feedback->setMessage((string) $data->message);
        $feedback->setPageUrl($pageUrl);
        $feedback->setUser($user);

        $email = $user?->getEmail() ?? $data->email;

        $feedback->setEmail($email);

        $this->entityManager->persist($feedback);
        $this->entityManager->flush();

        return $feedback;
    }
}
