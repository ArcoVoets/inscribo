<?php

namespace App\Filament\Resources\Events\RegistrationsResource\Pages;

use App\Actions\SyncMolliePaymentStatus;
use App\Enums\PermissionsEnum;
use App\Filament\Resources\Events\RegistrationsResource;
use App\Models\Registration;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
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
                Action::make('sync_payment_status')
                    ->label(__('admin.registrations.pages.edit.actions.sync_payment_status.label'))
                    ->icon(Heroicon::ArrowPath)
                    ->iconPosition(IconPosition::After)
                    ->action(function (Registration $record): void {
                        $action = app(SyncMolliePaymentStatus::class);
                        $action->execute($record->payments()->latest()->firstOrFail());

                        Notification::make()
                            ->title(__('admin.registrations.pages.edit.actions.sync_payment_status.messages.success'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Registration $record): bool => $record->payments()->exists() && Auth::user()->can(PermissionsEnum::SYNC_PAYMENT_STATUS)),
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
