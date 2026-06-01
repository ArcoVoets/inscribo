<?php

namespace App\Filament\Resources\Events\Tables;

use App\Models\Event;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                TextColumn::make('student_price_cents')
                    ->label(__('admin.events.tables.student_price_cents'))
                    ->money('EUR', divideBy: 100, locale: 'nl')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('worker_price_cents')
                    ->label(__('admin.events.tables.worker_price_cents'))
                    ->money('EUR', divideBy: 100, locale: 'nl')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                IconColumn::make('show_waitlist_position')
                    ->label(__('admin.events.tables.show_waitlist_position'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
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
                    ])
                    ->after(function (Event $record, Event $replica): void {
                        $record->form->deepReplicate($replica);
                        $replica->replicateMailTemplatesFrom($record);
                    }),
            ]);
    }
}
