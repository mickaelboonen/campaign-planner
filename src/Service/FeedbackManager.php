<?php

namespace App\Service;

use App\DTO\CreateFeedbackData;
use App\Entity\Feedback;
use App\Entity\User;
use App\Enum\FeedbackStatus;
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
    ): Feedback {
        $feedback = new Feedback();

        $email = $user?->getEmail() ?? $data->email;

        $feedback
            ->setType($data->type)
            ->setSubject(trim((string) $data->subject))
            ->setMessage(trim((string) $data->message))
            ->setEmail($email)
            ->setUser($user);

        $this->entityManager->persist($feedback);
        $this->entityManager->flush();

        return $feedback;
    }

    public function markAsRead(Feedback $feedback): void
    {
        if ($feedback->getStatus() !== FeedbackStatus::NEW) {
            return;
        }

        $feedback->setStatus(FeedbackStatus::READ);

        $this->entityManager->flush();
    }

    public function close(Feedback $feedback): void
    {
        if ($feedback->getStatus() === FeedbackStatus::CLOSED) {
            return;
        }

        $feedback->setStatus(FeedbackStatus::CLOSED);

        $this->entityManager->flush();
    }

    public function reopen(Feedback $feedback): void
    {
        if ($feedback->getStatus() !== FeedbackStatus::CLOSED) {
            return;
        }

        $feedback->setStatus(FeedbackStatus::READ);

        $this->entityManager->flush();
    }
}
