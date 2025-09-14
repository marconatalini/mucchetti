<?php

namespace App\Workflow\Transition;

class PermitRequestTransition
{
    public const string PERMIT_SUBMIT = 'submit';
    public const string PERMIT_APPROVE = 'approve';
    public const string PERMIT_REJECT = 'reject';
    public const string PERMIT_CLOSE = 'close';
    public const string PERMIT_REGISTER = 'register';

}
