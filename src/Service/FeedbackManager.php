<?php

namespace App\Service;

use App\DTO\CreateFeedbackData;
use App\Entity\Feedback;
use App\Entity\User;
use App\Enum\FeedbackStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class FeedbackManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
    ) {
    }

    public function create(
        CreateFeedbackData $data,
        ?User $user = null,
    ): Feedback {
        $feedback = new Feedback();
        $email = $user?->getEmail() ?? $data->email;
        $subject = trim((string) $data->subject);

        if ($subject === '') {
            $subject = $this->translator->trans(
                $data->type->defaultSubjectTranslationKey(),
                domain: 'enums',
            );
        }

        $feedback
            ->setType($data->type)
            ->setSubject($subject)
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
