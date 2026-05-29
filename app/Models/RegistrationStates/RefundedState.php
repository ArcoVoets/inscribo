<?php

namespace App\Models\RegistrationStates;

use App\Models\RegistrationState;
use Parental\HasParent;

class RefundedState extends RegistrationState
{
    use HasParent;

    public static string $filamentColor = 'primary';
}
