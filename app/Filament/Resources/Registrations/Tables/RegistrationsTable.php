<?php

namespace App\Filament\Resources\Registrations\Tables;

use App\Filament\Resources\Events\Pages\ManageEventRegistrations;
use App\Models\Registration;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.registrations.tables.name'))
                    ->hidden(fn (ManageRelatedRecords $livewire): bool => $livewire->getOwnerRecord()->form?->name_field_id === null)
                    ->state(fn (Registration $record): string => $record->name()),
                TextColumn::make('email')
                    ->label(__('admin.registrations.tables.email'))
                    ->hidden(fn (ManageRelatedRecords $livewire): bool => $livewire->getOwnerRecord()->form?->email_field_id === null)
                    ->state(fn (Registration $record): ?string => $record->notifyEmail()),
                TextColumn::make('currentState.type')
                    ->label(__('admin.registrations.tables.state'))
                    ->badge()
                    ->formatStateUsing(fn (Registration $record): string => $record->currentState?->getLabel())
                    ->color(fn (Registration $record): string => $record->currentState::$filamentColor),
                TextColumn::make('created_at')
                    ->label(__('admin.registrations.tables.registered_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            // Include email and name fields if any. Without this, Laravel will automatically eager load the whole
            // relationship, meaning all registrationValues will be loaded instead of just the email and name fields.
            ->modifyQueryUsing(fn (Builder $query, ManageEventRegistrations $livewire) => $query
                ->with([
                    'registrationValues' => fn (HasMany $registrationValues): HasMany => $registrationValues->whereIn('field_id', array_filter([
                        $livewire->record->form->name_field_id,
                        $livewire->record->form->email_field_id,
                    ])),
                ]));
    }
}
