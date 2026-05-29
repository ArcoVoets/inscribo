<?php

namespace App\Filament\Resources\Events\Tables;

use App\Filament\Resources\Events\RegistrationsResource;
use App\Models\Invite;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.events.tables.invites.name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('admin.events.tables.invites.email'))
                    ->searchable(),
                TextColumn::make('expires_at')
                    ->label(__('admin.events.tables.invites.expires_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('used_at')
                    ->label(__('admin.events.tables.invites.used_at'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('used_registration_id')
                    ->label(__('admin.events.tables.invites.used_registration_id'))
                    ->state(fn (Invite $record): string => $record->usedRegistration?->name() ?? '-')
                    ->url(fn (Invite $record): ?string => $record->used_registration_id
                        ? RegistrationsResource::getUrl('edit', [
                            'event' => $record->event_id,
                            'record' => $record->used_registration_id,
                        ])
                        : null),
                TextColumn::make('revoked_at')
                    ->label(__('admin.events.tables.invites.revoked_at'))
                    ->dateTime(),
                TextColumn::make('created_at')
                    ->label(__('admin.events.tables.invites.created_at'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('copy_link')
                    ->label(__('admin.events.tables.invites.copy_link'))
                    ->icon(Heroicon::Link)
                    ->hidden(fn (Invite $record): bool => $record->revoked_at !== null || $record->used_at !== null)
                    ->extraAttributes(function (Invite $record): array {
                        $url = $record->url();

                        return [
                            'onclick' => "navigator.clipboard.writeText('{$url}')",
                            'title' => 'Copy Name',
                        ];
                    })
                    ->actionJs(function (): string {
                        $message = __('admin.events.tables.invites.copy_link_notification');

                        return <<<JS
                            new FilamentNotification('copy_link_notification')
                                .title('{$message}')
                                .success()
                                .send();
                            JS;
                    }),

                Action::make('revoke')
                    ->label(__('admin.events.tables.invites.revoke'))
                    ->icon(Heroicon::NoSymbol)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->hidden(fn (Invite $record): bool => $record->revoked_at !== null || $record->used_at !== null)
                    ->action(fn (Invite $record): int => $record->update(['revoked_at' => now()])),
            ]);
    }
}
