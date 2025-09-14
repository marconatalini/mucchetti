<?php

namespace App\Workflow;

use App\Entity\Permit;
use App\Repository\PermitRepository;
use App\Workflow\State\PermitRequestState;
use App\Workflow\Transition\PermitRequestTransition;
use Symfony\Component\Workflow\WorkflowInterface;

class PermitRequestWorkflow
{
    public function __construct(
        readonly WorkflowInterface $permitRequestStateMachine,
        readonly PermitRepository $permitRepository
    )
    {
    }

    public function initiate(Permit $permit): void
    {
        $this->permitRequestStateMachine->getMarking($permit);
    }

    public function submit(Permit $permit): void
    {
        $this->permitRequestStateMachine->apply($permit, PermitRequestTransition::PERMIT_SUBMIT);
        $this->permitRepository->save($permit);
    }

    public function approve(Permit $permit): void
    {
        $this->permitRequestStateMachine->apply($permit, PermitRequestTransition::PERMIT_APPROVE);
        $this->permitRepository->save($permit);
    }

    public function reject(Permit $permit): void
    {
        $this->permitRequestStateMachine->apply($permit, PermitRequestTransition::PERMIT_REJECT);
        $this->permitRepository->save($permit);
    }

    public function close(Permit $permit): void
    {
        $this->permitRequestStateMachine->apply($permit, PermitRequestTransition::PERMIT_CLOSE);
        $this->permitRepository->save($permit);
    }


    public function delete(Permit $permit): void
    {
        $this->permitRepository->remove($permit, true);
    }




}
