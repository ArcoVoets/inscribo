<?php

namespace App\Filament\Resources\Events\Tables;

use App\Enums\RegistrationStates;
use App\Models\Event;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                foreach (RegistrationStates::cases() as $state) {
                    $query->withCount([
                        "registrations as {$state->value}_registrations_count" => fn (Builder $query): Builder => $query
                            ->whereState($state),
                    ]);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.events.tables.title'))
                    ->searchable(),
                TextColumn::make('capacity')
                    ->label(__('admin.events.tables.capacity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('opens_at')
                    ->label(__('admin.events.tables.opens_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('closes_at')
                    ->label(__('admin.events.tables.closes_at'))
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('show_waitlist_position')
                    ->label(__('admin.events.tables.show_waitlist_position'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                ...array_map(
                    fn (RegistrationStates $state): TextColumn => TextColumn::make("{$state->value}_registrations_count")
                        ->label($state->getLabel())
                        ->numeric()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: ! in_array($state, [
                            RegistrationStates::Registered,
                            RegistrationStates::Waitlisted,
                        ], true)),
                    RegistrationStates::tabsOrder(),
                ),
            ])
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel(),
                ReplicateAction::make()
                    ->schema([
                        TextInput::make('title')
                            ->label(__('admin.events.form.fields.title')),
                        DateTimePicker::make('opens_at')
                            ->label(__('admin.events.form.fields.opens_at'))
                            ->columnSpan(0)
                            ->required(),
                        DateTimePicker::make('closes_at')
                            ->label(__('admin.events.form.fields.closes_at')),
                        TextInput::make('year')
                            ->label(__('admin.events.form.fields.year'))
                            ->helperText(__('admin.events.form.fields.year_helper')),
                    ])
                    ->after(function (Event $record, Event $replica): void {
                        $record->form->deepReplicate($replica);
                        $replica->replicateMailTemplatesFrom($record);
                    }),
            ]);
    }
}
