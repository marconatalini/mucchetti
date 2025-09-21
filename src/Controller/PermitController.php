<?php

namespace App\Controller;

use App\Entity\Permit;
use App\Form\PermitType;
use App\Repository\PermitRepository;
use App\Repository\UserRepository;
use App\Workflow\PermitRequestWorkflow;
use App\Workflow\Transition\PermitRequestTransition;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Component\Workflow\WorkflowInterface;

#[IsGranted('ROLE_USER')]
final class PermitController extends AbstractController
{
    public function __construct(
        readonly PermitRepository  $permitRepository,
        readonly PermitRequestWorkflow $permitRequestStateMachine,
    )
    {
    }

    #[Route('/permit', name: 'app_permit_index')]
    public function index(): Response
    {
        return $this->render('permit/index.html.twig', [
            'permits' => $this->permitRepository->findActivePermitByUser($this->getUser()),
        ]);
    }

    #[Route('/permit/calendar', name: 'app_permit_calendar')]
    public function calendar(): Response
    {
        return $this->render('permit/calendar.html.twig', []);
    }

    #[Route('/permit/alert', name: 'app_permit_alert')]
    public function alertCount(): Response
    {
        $staffPermits = $this->permitRepository->findActiveStaffPermits($this->getUser()->getStaffMembers());

        return $this->render('permit/_alert.html.twig', [
            'count' => count($staffPermits),
        ]);
    }

    #[Route('/permit/action', name: 'app_permit_action_index')]
    public function needActionIndex(): Response
    {
        if ($this->isGranted("ROLE_STAFF")) {
            $staffPermits = $this->permitRepository->findAll();
        } else {
            $staffPermits = $this->permitRepository->findActiveStaffPermits($this->getUser()->getStaffMembers());
        }

        return $this->render('permit/needAction.html.twig', [
            'staffPermits' => $staffPermits
        ]);
    }

    #[Route('/permit/new', name: 'app_permit_new')]
    #[Route('/permit/edit/{id}', name: 'app_permit_edit')]
    public function new(?Permit $permit, Request $request): Response
    {
        if (null == $this->getUser()->getParentUser()){
            $this->addFlash('warning', 'Il tuo responsabile non è registrato su questa app.');
            return $this->redirectToRoute('app_home');
        }

        if (null === $permit) {
            $permit = new Permit();
        }

        $form = $this->createForm(PermitType::class, $permit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $permit = $form->getData();
            $permit->setEmployee($this->getUser());
            $this->permitRequestStateMachine->initiate($permit);
            $this->permitRepository->add($permit);

            return $this->redirectToRoute('app_permit_index', ['id' => $permit->getId()]);
        }

        return $this->render('permit/new.html.twig', [
            'form' => $form,
        ]);

    }

    #[Route('/permit/employee', name: 'app_permit_employee')]
    public function employee(Request $request, UserRepository $userRepository): Response
    {
        $search_array = explode(' ', $request->get('employee'), 2);
        if (count($search_array) < 2) {
            $this->addFlash('danger', 'Insert FirstName SecondName (1 space between)');
            return $this->redirectToRoute('app_home');
        }

        $user = $userRepository->findOneBy(['firstName' => $search_array[0], 'lastName' => $search_array[1]]);
        if (null === $user) {
            $this->addFlash('danger', 'User not found');
            return $this->redirectToRoute('app_home');
        }

        $permit = $this->permitRepository->findActualUserPermit($user);

        if (null === $permit) {
            $this->addFlash('info', $user . 'has not taken permit, now.');
            return $this->redirectToRoute('app_home');
        }

        $this->addFlash('success', sprintf('Yes, permit found! Ends at %s' , $permit->getEndAt()->format('d/m/y H:i')));

        return $this->render('default/home.html.twig');
    }

    #[Route('/permit/doAction/{id}/{action}', name: 'app_permit_action')]
    public function doAction(Permit $permit, string $action): Response
    {
        // TODO assert action is valid string
        eval('$this->permitRequestStateMachine->'.$action.'($permit);');
        return $this->redirectToRoute('app_permit_action_index');
    }

    #[Route('/permit/register/{id}', name: 'app_permit_register')]
    #[isGranted('ROLE_STAFF')]
    public function register(Permit $permit): Response
    {

        try {
            $this->permitRequestStateMachine->close($permit);
        } catch (NotEnabledTransitionException $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('app_permit_index');
        }

        $this->addFlash('success',"Permesso registrato, grazie.");
        return $this->redirectToRoute('app_home');
    }


    #[Route(path: '/permit/calendar/json', name: 'app_permit_calendar_json', methods: ['POST'])]
    public function customer_order_calendar_json(Request $request): JsonResponse
    {
        $start = $request->get('start');
        $end = $request->get('end');

        $events = $this->permitRepository->findByInterval($start, $end, $this->getUser());

        $mapped_result = [];
        foreach ($events as $event) {
//            $url = $this->adminUrlGenerator
//                ->setController(CustomerOrderCrudController::class)
//                ->setEntityId($event['id'])
//                ->setAction('edit')
//                ->generateUrl();
//            $event['url'] = $url;
            $mapped_result[] = $event;
        }
        return $this->json($mapped_result);
    }



}
