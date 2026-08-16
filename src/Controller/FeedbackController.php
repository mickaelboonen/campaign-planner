<?php

namespace App\Controller;

use App\DTO\CreateFeedbackData;
use App\Entity\User;
use App\Form\FeedbackType;
use App\Service\FeedbackManager;
use App\Service\Notification\EmailFeedbackNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FeedbackController extends AbstractController
{
    public function __construct(
        private readonly FeedbackManager $feedbackManager,
        private readonly EmailFeedbackNotifier $emailFeedbackNotifier,
    ) {
    }

    #[Route(
        '/contact',
        name: 'feedback_create',
        methods: ['GET', 'POST'],
    )]
    public function create(Request $request): Response
    {
        $data = new CreateFeedbackData();
        $user = $this->getUser();

        if ($user instanceof User) {
            $data->email = $user->getEmail();
        }

        $form = $this->createForm(
            FeedbackType::class,
            $data,
            ['authenticated' => $user instanceof User],
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $feedback = $this->feedbackManager->create(
                $data,
                $user instanceof User ? $user : null,
            );

            $this->emailFeedbackNotifier->notify($feedback);
            $this->addFlash('success', 'feedback.sent');

            return $this->redirectToRoute('feedback_create');
        }

        return $this->render('feedback/create.html.twig', [
            'form' => $form,
        ]);
    }
}
