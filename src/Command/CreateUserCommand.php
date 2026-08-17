<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'app:create-user',
)]
final class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly TranslatorInterface $translator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(
            $this->translator->trans(
                'create_user.description',
                domain: 'commands',
            ),
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $helper = $this->getHelper('question');

        $emailQuestion = new Question(
            $this->translator->trans(
                'create_user.email.question',
                domain: 'commands',
            ),
        );

        $emailQuestion->setValidator(function (?string $email): string {
            $email = trim((string) $email);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException(
                    $this->translator->trans(
                        'create_user.email.invalid',
                        domain: 'commands',
                    ),
                );
            }

            return mb_strtolower($email);
        });

        $email = $helper->ask(
            $input,
            $output,
            $emailQuestion,
        );

        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            $output->writeln(sprintf(
                '<error>%s</error>',
                $this->translator->trans(
                    'create_user.email.exists',
                    domain: 'commands',
                ),
            ));

            return Command::FAILURE;
        }

        $passwordQuestion = new Question(
            $this->translator->trans(
                'create_user.password.question',
                domain: 'commands',
            ),
        );

        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $passwordQuestion->setValidator(function (?string $password): string {
            if (mb_strlen((string) $password) < 12) {
                throw new \RuntimeException(
                    $this->translator->trans(
                        'create_user.password.min_length',
                        domain: 'commands',
                    ),
                );
            }

            return (string) $password;
        });

        $password = $helper->ask(
            $input,
            $output,
            $passwordQuestion,
        );

        $confirmationQuestion = new Question(
            $this->translator->trans(
                'create_user.password_confirmation.question',
                domain: 'commands',
            ),
        );

        $confirmationQuestion->setHidden(true);
        $confirmationQuestion->setHiddenFallback(false);

        $passwordConfirmation = $helper->ask(
            $input,
            $output,
            $confirmationQuestion,
        );

        if ($password !== $passwordConfirmation) {
            $output->writeln(sprintf(
                '<error>%s</error>',
                $this->translator->trans(
                    'create_user.password_confirmation.mismatch',
                    domain: 'commands',
                ),
            ));

            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_GAME_MASTER']);
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                $password,
            ),
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf(
            '<info>%s</info>',
            $this->translator->trans(
                'create_user.success',
                ['%email%' => $email],
                'commands',
            ),
        ));

        return Command::SUCCESS;
    }
}
