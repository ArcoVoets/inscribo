<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Traits\HasEventSetupWarnings;
use Filament\Resources\Pages\ViewRecord;

class ViewEvent extends ViewRecord
{
    use HasEventSetupWarnings;

    protected static string $resource = EventResource::class;
}
