<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmployeeType;
use App\Repository\PermitRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class EmployeeController extends AbstractController
{

    public function __construct(
        readonly UserRepository $userRepository,
        readonly UserPasswordHasherInterface $userPasswordHasher,
        readonly TranslatorInterface $translator
    )
    {
    }

    #[Route('/employee/new', name: 'app_employee_new')]
    #[Route('/employee/edit/{id}', name: 'app_employee_edit')]
    public function edit(?User $user, Request $request): Response
    {
        if (null == $user) {
            $employee = new User();
            $edit_mode = false;
        } else {
            $employee = $user;
            $edit_mode = true;
        }

        $form = $this->createForm(EmployeeType::class, $employee, [
            'is_new' => !$edit_mode
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            if (!$edit_mode) {
                $hashedPassword = $this->userPasswordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($hashedPassword);
            }

            $this->userRepository->add(user: $user);
            $this->addFlash('success', 'employee.saved');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('employee/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/employee/reset', name: 'app_employee_reset')]
    public function reset(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'first_options' => ['label' => 'Password'],
                    'second_options' => ['label' => 'Repeat Password'],
            ])
            ->add('save', SubmitType::class, ['label' => 'Reset'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($form->getData());
            $plain_password = $form->getData()['password'];
            $user = $this->getUser();
            $hashedPassword = $this->userPasswordHasher->hashPassword($user, $plain_password);
            $user->setPassword($hashedPassword);

            $this->userRepository->add(user: $user);
            $this->addFlash('success', 'employee.password.reset');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('employee/reset.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/employee/search', name: 'app_permit_employee')]
    public function employee(Request $request, UserRepository $userRepository, PermitRepository $permitRepository): Response
    {
        $search_array = explode(' ', $request->get('employee'), 2);
        if (count($search_array) < 2) {
            $this->addFlash('danger', 'write.initial.select.from.list');
            return $this->redirectToRoute('app_home');
        }

        $user = $userRepository->findOneBy(['firstName' => $search_array[0], 'lastName' => $search_array[1]]);
        if (null === $user) {
            $this->addFlash('danger', 'user.not.found');
            return $this->redirectToRoute('app_home');
        }

        $permit = $permitRepository->findActualUserPermit($user);

        if (null === $permit) {
            $this->addFlash('info', $this->translator->trans('user.has.not.permit',['%user%' => $user]));
            return $this->redirectToRoute('app_home');
        }

        $this->addFlash('success', sprintf($this->translator->trans('user.has.permit.since.end', [
            '%user%' => $user,
            '%end%' => $permit->getEndAt()->format('d/m/y H:i')])));

        return $this->render('default/home.html.twig');
    }
}
