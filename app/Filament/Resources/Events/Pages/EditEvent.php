<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Traits\HasEventSetupWarnings;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    use HasEventSetupWarnings;

    protected static string $resource = EventResource::class;

    protected function afterSave(): void
    {
        $this->eventSetupWarnings = null;

        // Refresh the record from the database to reflect changes from nested repeaters
        // Without this, the form form shows the old values after saving, making it look
        // like the values weren't saved (even though they were)
        $this->record->refresh();
        $this->fillForm();
    }
}
