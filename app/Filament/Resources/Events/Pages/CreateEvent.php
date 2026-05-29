<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Traits\HasEventSetupWarnings;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    use HasEventSetupWarnings;

    protected static string $resource = EventResource::class;
}
