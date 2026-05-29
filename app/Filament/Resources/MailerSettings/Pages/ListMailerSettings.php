<?php

namespace App\Filament\Resources\MailerSettings\Pages;

use App\Filament\Resources\MailerSettings\MailerSettingsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMailerSettings extends ListRecords
{
    protected static string $resource = MailerSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
