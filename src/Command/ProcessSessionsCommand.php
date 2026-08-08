<?php

namespace App\Command;

use App\Service\SessionAutomationManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:sessions:process',
    description: 'Traite les rappels et les sessions passées.',
)]
final class ProcessSessionsCommand extends Command
{
    public function __construct(
        private readonly SessionAutomationManager $sessionAutomationManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->sessionAutomationManager->process();

        $output->writeln('<info>Traitement des sessions terminé.</info>');

        return Command::SUCCESS;
    }
}
