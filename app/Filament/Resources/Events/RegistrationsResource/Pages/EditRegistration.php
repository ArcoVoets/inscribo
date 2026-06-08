<?php

namespace App\Filament\Resources\Events\RegistrationsResource\Pages;

use App\Filament\Resources\Events\RegistrationsResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class EditRegistration extends EditRecord
{
    protected static string $resource = RegistrationsResource::class;

    protected function getHeaderActions(): array
    {
        return array_merge(
            $this->getRecord()?->currentState->filamentHeaderActions() ?? [],
            [
                DeleteAction::make()
                    ->modalDescription(new HtmlString(__('admin.registrations.pages.edit.actions.delete.modal.description'))),
                Action::make('status_page')
                    ->label(__('admin.registrations.pages.edit.actions.status_page.label'))
                    ->link()
                    ->icon(Heroicon::ArrowTopRightOnSquare)
                    ->iconPosition(IconPosition::After)
                    ->url($this->getRecord()->publicStatusUrl())
                    ->openUrlInNewTab(),
            ],
        );
    }
}
