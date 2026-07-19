<?php

namespace App\Workflow;

use App\Entity\Stamping;
use App\Repository\StampingRepository;
use App\Workflow\Transition\MissedStampingTransition;
use Symfony\Component\Workflow\WorkflowInterface;

class MissedStampingWorkflow
{
    public function __construct(
        readonly WorkflowInterface $missedStampingStateMachine,
        readonly StampingRepository $stampingRepository
    )
    {
    }

    public function initiate(Stamping $stamping): void
    {
        $this->missedStampingStateMachine->getMarking($stamping);
    }

    public function submit(Stamping $stamping): void
    {
        $this->missedStampingStateMachine->apply($stamping, MissedStampingTransition::STAMPING_SUBMIT);
        $this->stampingRepository->save($stamping);
    }

    public function approve(Stamping $stamping): void
    {
        $this->missedStampingStateMachine->apply($stamping, MissedStampingTransition::STAMPING_APPROVE);
        $this->stampingRepository->save($stamping);
    }

    public function reject(Stamping $stamping): void
    {
        $this->missedStampingStateMachine->apply($stamping, MissedStampingTransition::STAMPING_REJECT);
        $this->stampingRepository->save($stamping);
    }

    public function close(Stamping $stamping): void
    {
        $this->missedStampingStateMachine->apply($stamping, MissedStampingTransition::STAMPING_CLOSE);
        $this->stampingRepository->save($stamping);
    }


    public function delete(Stamping $stamping): void
    {
        $this->stampingRepository->remove($stamping);
    }




}
