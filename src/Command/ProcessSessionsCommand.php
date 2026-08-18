<?php

namespace App\Command;

use App\Service\SessionAutomationManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'app:sessions:process',
)]
final class ProcessSessionsCommand extends Command
{
    public function __construct(
        private readonly SessionAutomationManager $sessionAutomationManager,
        private readonly TranslatorInterface $translator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(
            $this->translator->trans(
                'sessions.description',
                domain: 'commands',
            ),
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $this->sessionAutomationManager->process();

        $output->writeln(sprintf(
            '<info>%s</info>',
            $this->translator->trans(
                'sessions.success',
                domain: 'commands',
            ),
        ));

        return Command::SUCCESS;
    }
}
