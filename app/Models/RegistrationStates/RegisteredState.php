<?php

namespace App\Models\RegistrationStates;

use App\Models\RegistrationState;
use Filament\Actions\Action;
use Parental\HasParent;

class RegisteredState extends RegistrationState
{
    use HasParent;

    public static string $filamentColor = 'success';

    /**
     * @return array<int, Action>
     */
    public function filamentHeaderActions(): array
    {
        return [
            self::cancelAction(),
        ];
    }
}
