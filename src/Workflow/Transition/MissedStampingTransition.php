<?php

namespace App\Workflow\Transition;

class MissedStampingTransition
{
    public const string STAMPING_SUBMIT = 'submit';
    public const string STAMPING_APPROVE = 'approve';
    public const string STAMPING_REJECT = 'reject';
    public const string STAMPING_CLOSE = 'close';
    public const string STAMPING_REGISTER = 'register';

}
