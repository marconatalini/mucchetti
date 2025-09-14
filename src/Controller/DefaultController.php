<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        if (null !== $request->get('employee')) {
            return $this->redirectToRoute('app_permit_employee', [
                'employee' => $request->get('employee')
            ]);
        }

        return $this->render('default/home.html.twig', [
            'employees' => $userRepository->findAll(),
        ]);
    }
}
