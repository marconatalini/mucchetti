<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'employee:reset',
    description: 'Reset employee password to "password"',
)]
class EmployeeResetCommand extends Command
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        readonly UserRepository $userRepository

    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Email of employee to reset')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        if ($email) {
            $user = $this->userRepository->findOneBy(['email' => $email]);
            $password = $this->userPasswordHasher->hashPassword($user, 'password');
//            $io->note(sprintf('You passed an argument: %s', $arg1));
            $user->setPassword($password);
        }


        $this->userRepository->add(user: $user);

        $io->success(sprintf('User %s has reset his password to "password"', $user));

        return Command::SUCCESS;
    }
}
