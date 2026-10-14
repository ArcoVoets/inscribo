<?php

namespace App\Models\RegistrationStates;

use App\Filament\Actions\InviteAction;
use App\Models\RegistrationState;
use Filament\Actions\Action;
use Parental\HasParent;

class WaitlistedState extends RegistrationState
{
    use HasParent;

    public static string $filamentColor = 'gray';

    /**
     * @return array<int, Action>
     */
    public function filamentHeaderActions(): array
    {
        return [
            InviteAction::make('invite', $this->registration),
            self::cancelAction(),
        ];
    }
}
