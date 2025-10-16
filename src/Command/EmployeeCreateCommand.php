<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'employee:create',
    description: 'Create a new employee',
)]
class EmployeeCreateCommand extends Command
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        readonly UserRepository $userRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            ->addOption('role', null, InputOption::VALUE_OPTIONAL, 'Role')
            ->addOption('firstname', null, InputOption::VALUE_OPTIONAL, 'First name')
            ->addOption('lastname', null, InputOption::VALUE_OPTIONAL, 'Last name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $plain_password = $input->getArgument('password');

        $user = new User();
        if ($email && $plain_password) {
//            $io->note(sprintf('You passed an argument: %s', $email));
            $password = $this->passwordHasher->hashPassword($user, $plain_password);
            $user->setEmail($email);
            $user->setPassword($password);
            $fullname = explode('@', $email)[0];
            $user->setFirstName(ucfirst(explode('.',$fullname)[0]));
            $user->setLastName(ucfirst(explode('.',$fullname)[1]));
        }

        if ($input->getOption('firstname') && $input->getOption('lastname')) {
            $user->setFirstname($input->getOption('firstname'));
            $user->setLastname($input->getOption('lastname'));
        }

        if ($input->getOption('role')) {
            $user->setRoles([$input->getOption('role')]);
        }

        $this->userRepository->add(user: $user);

        $io->success('You have create user successfully');

        return Command::SUCCESS;
    }
}
