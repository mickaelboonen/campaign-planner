<?php

namespace App\Controller;

use App\DTO\CreateParticipantData;
use App\DTO\EditParticipantData;
use App\Entity\Campaign;
use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Security\Voter\CampaignVoter;
use App\Service\Notification\EmailParticipantAccessNotifier;
use App\Service\ParticipantManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        TranslatorInterface $translator,
    ): Response {
        $this->denyAccessUnlessGranted(CampaignVoter::EDIT, $campaign);

        $data = new CreateParticipantData();
        $form = $this->createForm(ParticipantType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $archivedParticipant = $this->participantRepository
                ->findArchivedByCampaignAndEmail(
                    $campaign,
                    (string) $data->email,
                );

            if ($archivedParticipant !== null) {
                $form->get('email')->addError(
                    new FormError(
                        $translator->trans(
                            'errors.archived_email',
                            domain: 'participant',
                        ),
                    ),
                );
            } elseif (
                $this->participantRepository
                    ->existsByCampaignAndEmail(
                        $campaign,
                        (string) $data->email,
                    )
            ) {
                $form->get('email')->addError(
                    new FormError(
                        $translator->trans(
                            'errors.email_used',
                            domain: 'participant',
                        ),
                    ),
                );
            } else {
                $participant = $this->participantManager
                    ->create($campaign, $data);

                $this->emailParticipantAccessNotifier
                    ->notifyInvitation($participant);

                $this->addFlash('success', 'participant.created');

                return $this->redirectToRoute('campaign_show', [
                    'id' => $campaign->getId(),
                ]);
            }
        }

        return $this->render('participant/new.html.twig', [
            'campaign' => $campaign,
            'form' => $form,
        ]);
    }

    #[Route('/{participant}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Campaign $campaign,
        Participant $participant,
        TranslatorInterface $translator,
    ): Response {
        $this->denyAccessUnlessGranted(CampaignVoter::EDIT, $campaign);

        if ($participant->getCampaign() !== $campaign) {
            throw $this->createNotFoundException();
        }

        $data = EditParticipantData::fromParticipant($participant);

        $form = $this->createForm(
            ParticipantType::class,
            $data,
            ['data_class' => EditParticipantData::class],
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
                        $translator->trans(
                            'errors.email_used',
                            domain: 'participant',
                        ),
                    ),
                );
            } else {
                $this->participantManager->update($participant, $data);
                $this->addFlash('success', 'participant.updated');

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
        TranslatorInterface $translator,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(CampaignVoter::EDIT, $campaign);

        if ($participant->getCampaign() !== $campaign) {
            throw $this->createNotFoundException();
        }

        $this->denyInvalidCsrf(
            'archive-participant-'.$participant->getId(),
            $request->request->get('_token'),
            $translator,
        );

        $this->participantManager->archive($participant);
        $this->addFlash('success', 'participant.archived');

        return $this->redirectToRoute('campaign_show', [
            'id' => $campaign->getId(),
        ]);
    }

    #[Route(
        '/{participant}/restore',
        name: 'restore',
        methods: ['POST'],
    )]
    public function restore(
        Request $request,
        Campaign $campaign,
        Participant $participant,
        TranslatorInterface $translator,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(CampaignVoter::EDIT, $campaign);

        if ($participant->getCampaign() !== $campaign) {
            throw $this->createNotFoundException();
        }

        $this->denyInvalidCsrf(
            'restore-participant-'.$participant->getId(),
            $request->request->get('_token'),
            $translator,
        );

        $this->participantManager->restore($participant);
        $this->addFlash('success', 'participant.restored');

        return $this->redirectToRoute('campaign_show', [
            'id' => $campaign->getId(),
        ]);
    }
}
