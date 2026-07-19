<?php

namespace App\Workflow\State;

class MissedStampingState
{
    public const string START = 'start';
    public const string SUBMITTED = 'submitted';
    public const string REVIEW = 'review';
    public const string APPROVED = 'approved';
    public const string REGISTERED = 'registered';

}
