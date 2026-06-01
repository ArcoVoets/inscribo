<?php

namespace App\Filament\Resources\ApiKeys\Tables;

use App\Models\ApiKey;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApiKeysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('admin.api_keys.fields.name')),
                TextColumn::make('events_count')->counts('events')->label(__('admin.api_keys.table.num_events')),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('update_key')
                    ->label(__('admin.api_keys.table.update_key'))
                    ->schema([
                        TextInput::make('new_key')
                            ->password()
                            ->label(__('admin.api_keys.fields.key')),
                    ])
                    ->action(function (ApiKey $record, array $data) {
                        $record->update(['key' => $data['new_key']]);
                    })
                    ->icon(Heroicon::OutlinedKey),
            ]);
    }
}
