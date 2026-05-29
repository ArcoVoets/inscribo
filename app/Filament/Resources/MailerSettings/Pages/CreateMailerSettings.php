<?php

namespace App\Filament\Resources\MailerSettings\Pages;

use App\Filament\Resources\MailerSettings\MailerSettingsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMailerSettings extends CreateRecord
{
    protected static string $resource = MailerSettingsResource::class;
}
