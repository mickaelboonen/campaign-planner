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

#[AsCommand(
    name: 'app:create-user',
    description: 'Crée un compte de maître du jeu.',
)]
final class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $helper = $this->getHelper('question');

        $emailQuestion = new Question('Adresse email : ');
        $emailQuestion->setValidator(function (?string $email): string {
            $email = trim((string) $email);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('L’adresse email n’est pas valide.');
            }

            return mb_strtolower($email);
        });

        $email = $helper->ask($input, $output, $emailQuestion);

        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            $output->writeln('<error>Un utilisateur existe déjà avec cette adresse.</error>');

            return Command::FAILURE;
        }

        $passwordQuestion = new Question('Mot de passe : ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $passwordQuestion->setValidator(function (?string $password): string {
            if (mb_strlen((string) $password) < 12) {
                throw new \RuntimeException(
                    'Le mot de passe doit contenir au moins 12 caractères.'
                );
            }

            return (string) $password;
        });

        $password = $helper->ask($input, $output, $passwordQuestion);

        $confirmationQuestion = new Question('Confirme le mot de passe : ');
        $confirmationQuestion->setHidden(true);
        $confirmationQuestion->setHiddenFallback(false);

        $passwordConfirmation = $helper->ask(
            $input,
            $output,
            $confirmationQuestion
        );

        if ($password !== $passwordConfirmation) {
            $output->writeln('<error>Les mots de passe ne correspondent pas.</error>');

            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_GAME_MASTER']);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $password)
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(
            sprintf('<info>Le compte %s a bien été créé.</info>', $email)
        );

        return Command::SUCCESS;
    }
}