<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Models\Registration;
use App\Models\RegistrationState;
use App\Models\RegistrationValue;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        /** @var Registration $record */
        $record = $schema->getRecord();

        return $schema
            ->components([
                RepeatableEntry::make('registrationValues')
                    ->label(__('admin.registrations.form.sections.details'))
                    ->table([
                        TableColumn::make(__('admin.registrations.form.table.field')),
                        TableColumn::make(__('admin.registrations.form.table.value')),
                    ])
                    ->schema([
                        TextEntry::make('field.label'),
                        TextEntry::make('value')
                            ->state(fn (RegistrationValue $record): string => $record->showValue()),
                    ]),

                Grid::make()
                    ->columns(1)
                    ->schema([
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                TextEntry::make('currentState.amount_cents')
                                    ->label(__('admin.registrations.form.fields.amount'))
                                    ->state(fn (Registration $record): string => Number::currency($record->price_cents / 100)),
                                ...$record->currentState->stateFields(),
                            ]),
                        TextEntry::make('created_at')
                            ->label(__('admin.registrations.form.fields.submitted_at'))
                            ->dateTime(),
                        Textarea::make('manager_notes')
                            ->label(__('admin.registrations.form.fields.manager_notes'))
                            ->helperText(__('admin.registrations.form.fields.manager_notes_helper')),
                    ]),

                RepeatableEntry::make('states')
                    ->table([
                        TableColumn::make(__('admin.registrations.form.table.type')),
                        TableColumn::make(__('admin.registrations.form.table.at')),
                        TableColumn::make(__('admin.registrations.form.table.expires_at')),
                        TableColumn::make(__('admin.registrations.form.table.amount')),
                    ])
                    ->label(__('admin.registrations.form.sections.state_history'))
                    ->schema([
                        TextEntry::make('type')
                            ->label(__('admin.registrations.form.fields.state'))
                            ->badge()
                            ->formatStateUsing(fn (RegistrationState $record): string => $record->getLabel())
                            ->color(fn (RegistrationState $record): string => $record::$filamentColor),
                        TextEntry::make('created_at')
                            ->label(__('admin.registrations.form.fields.at'))
                            ->dateTime(),
                        TextEntry::make('expires_at')
                            ->label(__('admin.registrations.form.fields.expires'))
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('amount_cents')
                            ->label(__('admin.registrations.form.fields.amount'))
                            ->placeholder('-')
                            ->formatStateUsing(fn ($state): ?string => $state === null ? null : Number::currency($state / 100)),
                    ])
                    ->columnSpanFull(),

                RepeatableEntry::make('payments')
                    ->table([
                        TableColumn::make(__('admin.registrations.form.fields.status')),
                        TableColumn::make(__('admin.registrations.form.fields.at')),
                        TableColumn::make(__('admin.registrations.form.fields.mollie_payment_id')),
                    ])
                    ->label(__('admin.registrations.form.sections.payment_history'))
                    ->schema([
                        TextEntry::make('status')
                            ->label(__('admin.registrations.form.fields.status'))
                            ->badge(),
                        TextEntry::make('occured_at')
                            ->label(__('admin.registrations.form.fields.at'))
                            ->dateTime(),
                        TextEntry::make('mollie_payment_id')
                            ->label(__('admin.registrations.form.fields.mollie_payment_id')),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
