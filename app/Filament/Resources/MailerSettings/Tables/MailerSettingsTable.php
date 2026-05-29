<?php

namespace App\Filament\Resources\MailerSettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MailerSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('host')
                    ->label(__('admin.mailer_settings.tables.host'))
                    ->searchable(),
                TextColumn::make('port')
                    ->label(__('admin.mailer_settings.tables.port'))
                    ->sortable(),
                TextColumn::make('username')
                    ->label(__('admin.mailer_settings.tables.username'))
                    ->searchable(),
                TextColumn::make('from_address')
                    ->label(__('admin.mailer_settings.tables.from_address'))
                    ->searchable(),
                TextColumn::make('from_name')
                    ->label(__('admin.mailer_settings.tables.from_name'))
                    ->toggleable(),
                TextColumn::make('reply_to_address')
                    ->label(__('admin.mailer_settings.tables.reply_to_address'))
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
