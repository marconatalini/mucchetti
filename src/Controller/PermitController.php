<?php

namespace App\Controller;

use App\Entity\Permit;
use App\Form\PermitType;
use App\Repository\PermitRepository;
use App\Workflow\PermitRequestWorkflow;
use App\Workflow\State\PermitRequestState;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_USER')]
final class PermitController extends AbstractController
{
    public function __construct(
        readonly PermitRepository  $permitRepository,
        readonly PermitRequestWorkflow $permitRequestStateMachine,
        readonly TranslatorInterface $translator,
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

//    #[Route('/permit/alert', name: 'app_permit_alert')]
//    public function alertCount(): Response
//    {
//        $staffPermits = $this->permitRepository->findActiveStaffPermits(
//            $this->getUser()->getStaffMembers(), PermitRequestState::SUBMITTED);
//
//        return $this->render('permit/_alert.html.twig', [
//            'count' => count($staffPermits),
//        ]);
//    }

    #[Route('/permit/action', name: 'app_permit_action_index')]
    public function needActionIndex(): Response
    {
        if ($this->isGranted("ROLE_STAFF")) {
            $staffPermits = $this->permitRepository->findAll();
        } else {
            $staffPermits = $this->permitRepository->findActiveStaffPermits(
                $this->getUser()->getStaffMembers(), PermitRequestState::SUBMITTED);
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
            $this->addFlash('warning', $this->translator->trans('no.boss.registered'));
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

            return $this->redirectToRoute('app_permit_index');
        }

        return $this->render('permit/new.html.twig', [
            'form' => $form,
        ]);

    }


    #[Route('/permit/doAction/{id}/{action}', name: 'app_permit_action')]
    public function doAction(Permit $permit, string $action): Response
    {

        try {
            eval('$this->permitRequestStateMachine->' . $action . '($permit);');
        } catch (LogicException $e) {
            $this->addFlash('danger', $this->translator->trans('can.not.action', [
                '%action%' => $action]));
            return $this->redirectToRoute('app_home');
        }

        $this->addFlash('success', $this->translator->trans('do.action.on.permitId', [
            '%permitId%' => $permit->getId(),
            '%action%' => $action,
        ]));
//            sprintf("Hai eseguito l'azione '%s' sul permesso %d", $action, $permit->getId()));

        if ($this->getUser()->getParentUser()){
            return $this->redirectToRoute('app_permit_index');
        }

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
