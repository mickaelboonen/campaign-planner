<?php

namespace App\Controller;

use App\DTO\CreateParticipantData;
use App\DTO\EditParticipantData;
use App\Entity\Participant;
use Symfony\Component\Form\FormError;
use App\Entity\Campaign;
use App\Form\ParticipantType;
use App\Security\Voter\CampaignVoter;
use App\Service\Notification\EmailParticipantAccessNotifier;
use App\Service\ParticipantManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/campaign/{id}/participants', name: 'participant_')]
final class ParticipantController extends BaseController
{
    public function __construct(
        private readonly ParticipantManager $participantManager,
        private readonly ParticipantRepository $participantRepository,
        private readonly EmailParticipantAccessNotifier $emailParticipantAccessNotifier,
    ) {
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        Campaign $campaign,
    ): Response {
        $this->denyAccessUnlessGranted(
            CampaignVoter::EDIT,
            $campaign,
        );

        $data = new CreateParticipantData();

        $form = $this->createForm(
            ParticipantType::class,
            $data,
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (
                $this->participantRepository->existsByCampaignAndEmail(
                    $campaign,
                    (string) $data->email,
                )
            ) {
                $form->get('email')->addError(
                    new FormError(
                        'Cette adresse email est déjà utilisée dans cette campagne.',
                    )
                );
            } else {
                $participant = $this->participantManager->create($campaign, $data);

                $this->emailParticipantAccessNotifier->notifyInvitation($participant);

                $this->addFlash(
                    'success',
                    'Le participant a bien été ajouté.',
                );

                return $this->redirectToRoute('campaign_show', [
                    'id' => $campaign->getId(),
                ]);
            }
        }

        return $this->render(
            'participant/new.html.twig',
            [
                'campaign' => $campaign,
                'form' => $form,
            ]
        );
    }

    #[Route('/{participant}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Campaign $campaign,
        Participant $participant,
    ): Response {
        $this->denyAccessUnlessGranted(
            CampaignVoter::EDIT,
            $campaign,
        );

        if ($participant->getCampaign() !== $campaign) {
            throw $this->createNotFoundException();
        }

        $data = EditParticipantData::fromParticipant($participant);

        $form = $this->createForm(
            ParticipantType::class,
            $data,
            [
                'data_class' => EditParticipantData::class,
            ],
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailAlreadyUsed = $this->participantRepository
                ->existsByCampaignAndEmailExcludingParticipant(
                    $campaign,
                    (string) $data->email,
                    $participant,
                );

            if ($emailAlreadyUsed) {
                $form->get('email')->addError(
                    new FormError(
                        'Cette adresse email est déjà utilisée dans cette campagne.',
                    ),
                );
            } else {
                $this->participantManager->update($participant, $data);

                $this->addFlash(
                    'success',
                    'Le participant a bien été modifié.',
                );

                return $this->redirectToRoute('campaign_show', [
                    'id' => $campaign->getId(),
                ]);
            }
        }

        return $this->render('participant/edit.html.twig', [
            'campaign' => $campaign,
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route(
        '/{participant}/archive',
        name: 'archive',
        methods: ['POST'],
    )]
    public function archive(
        Request $request,
        Campaign $campaign,
        Participant $participant,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(
            CampaignVoter::EDIT,
            $campaign,
        );

        if ($participant->getCampaign() !== $campaign) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid(
            'archive-participant-'.$participant->getId(),
            (string) $request->request->get('_token'),
        )) {
            throw $this->createAccessDeniedException(
                'Jeton CSRF invalide.',
            );
        }

        $this->participantManager->archive($participant);

        $this->addFlash(
            'success',
            'Le participant a bien été archivé.',
        );

        return $this->redirectToRoute('campaign_show', [
            'id' => $campaign->getId(),
        ]);
    }
}
