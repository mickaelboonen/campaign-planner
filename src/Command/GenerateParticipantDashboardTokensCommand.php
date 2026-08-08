<?php

namespace App\Command;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:participant:generate-dashboard-tokens',
    description: 'Génère les dashboardToken des participants existants.',
)]
final class GenerateParticipantDashboardTokensCommand extends Command
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        /** @var Participant[] $participants */
        $participants = $this->participantRepository->findBy(
            [],
            [
                'email' => 'ASC',
            ],
        );

        $tokens = [];

        foreach ($participants as $participant) {
            if ($participant->getDashboardToken() !== null) {
                $tokens[
                    mb_strtolower(
                        trim($participant->getEmail()),
                    )
                ] = $participant->getDashboardToken();
                continue;
            }

            $email = mb_strtolower(
                trim($participant->getEmail()),
            );

            if (!isset($tokens[$email])) {
                $tokens[$email] = bin2hex(
                    random_bytes(32),
                );
            }

            $participant->setDashboardToken(
                $tokens[$email],
            );
        }

        $this->entityManager->flush();

        $output->writeln(sprintf(
            '%d participant(s) traité(s).',
            count($participants),
        ));

        return Command::SUCCESS;
    }
}
