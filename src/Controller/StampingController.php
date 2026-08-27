<?php

namespace App\Controller;

use App\Entity\Stamping;
use App\Form\StampingType;
use App\Repository\StampingRepository;
use App\Workflow\MissedStampingWorkflow;
use App\Workflow\State\MissedStampingState;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/stamping')]
#[IsGranted('ROLE_USER')]
final class StampingController extends AbstractController
{
    public function __construct(
        readonly MissedStampingWorkflow $missedStampingStateMachine,
        readonly TranslatorInterface $translator,
        private readonly StampingRepository $stampingRepository
    )
    {
    }

    #[Route('/index', name: 'app_stamping_index')]
    public function index(): Response
    {
        if ($this->isGranted('ROLE_STAFF')) {
            return $this->redirectToRoute('app_stamping_action_index');
        }

        return $this->render('stamping/index.html.twig', [
            'stampings' => $this->stampingRepository->findBy([
                'employee' => $this->getUser()
            ],['id' => 'DESC']),
        ]);
    }

    #[Route('/action/staff', name: 'app_stamping_staff_index')]
    #[IsGranted('ROLE_STAFF')]
    public function staffIndex(): Response
    {
        $staffMissedStamping = $this->stampingRepository->findBy([],['id' => 'DESC']);

        return $this->render('stamping/needAction.html.twig', [
            'staffMissedStamping' => $staffMissedStamping,
        ]);
    }

    #[Route('/action/boss', name: 'app_stamping_boss_index')]
    public function bossIndex(): Response
    {
        $staffMissedStamping = $this->stampingRepository->findStaffMissedStamping(
            $this->getUser()->getStaffMembers(), MissedStampingState::SUBMITTED);

        return $this->render('stamping/boss_index.html.twig', [
            'staffMissedStamping' => $staffMissedStamping,
        ]);
    }



    #[Route('/new', name: 'app_stamping_new')]
    #[Route('/edit/{id}', name: 'app_stamping_edit')]
    public function new(?Stamping $stamping, Request $request): Response
    {
        if (null == $this->getUser()->getParentUser()){
            $this->addFlash('warning', $this->translator->trans('no.boss.registered'));
            return $this->redirectToRoute('app_home');
        }

        if (null === $stamping) {
            $stamping = new Stamping();
        }

        $form = $this->createForm(StampingType::class, $stamping);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stamping = $form->getData();
            dump($stamping);
            $stamping->setEmployee($this->getUser());
            $this->missedStampingStateMachine->initiate($stamping);
            $this->stampingRepository->add($stamping);

            return $this->redirectToRoute('app_stamping_index');
        }

        return $this->render('stamping/new.html.twig', [
            'form' => $form,
        ]);

    }

    #[Route('/doAction/{id}/{action}', name: 'app_stamping_action')]
    public function doAction(Stamping $stamping, string $action): Response
    {

        try {
            eval('$this->missedStampingStateMachine->' . $action . '($stamping);');
        } catch (LogicException $e) {
            $this->addFlash('danger', $this->translator->trans('can.not.action', [
                '%action%' => $action]));
            return $this->redirectToRoute('app_home');
        }

        $this->addFlash('success', $this->translator->trans('do.action.on.stampingId', [
            '%stampingId%' => $stamping->getId(),
            '%action%' => $action,
        ]));
//            sprintf("Hai eseguito l'azione '%s' sul permesso %d", $action, $stamping->getId()));

        if ($this->getUser()->getParentUser()){
            return $this->redirectToRoute('app_stamping_index');
        }

        return $this->redirectToRoute('app_stamping_action_index');

    }

    #[Route('/print/{id}', name: 'app_stamping_print')]
//    #[isGranted('ROLE_STAFF')]
    public function print(Stamping $stamping): Response
    {
        return $this->render('stamping/print.html.twig', [
            'stamping' => $stamping,
        ]);
    }
}
