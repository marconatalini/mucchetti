<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmployeeType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use function Zenstruck\Foundry\Persistence\flush_after;

final class EmployeeController extends AbstractController
{

    public function __construct(
        readonly UserRepository $userRepository,
        readonly UserPasswordHasherInterface $userPasswordHasher
    )
    {
    }

    #[Route('/employee/new', name: 'app_employee_new')]
    #[Route('/employee/edit/{id}', name: 'app_employee_edit')]
    public function edit(?User $user, Request $request): Response
    {
        if (null == $user) {
            $employee = new User();
        } else {
            $employee = $user;
        }

        $form = $this->createForm(EmployeeType::class, $employee, [
            'is_new' => (bool)$user
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $hashedPassword = $this->userPasswordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            $this->userRepository->add(user: $user);
            $this->addFlash('success', 'Employee has been saved. Login');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('employee/new.html.twig', [
            'form' => $form,
        ]);
    }
}
