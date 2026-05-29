<?php

namespace App\Filament\Resources\MailerSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

class MailerSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('host')
                    ->label(__('admin.mailer_settings.form.fields.host'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('port')
                    ->label(__('admin.mailer_settings.form.fields.port'))
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(65535)
                    ->required(),
                TextInput::make('username')
                    ->label(__('admin.mailer_settings.form.fields.username'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->label(__('admin.mailer_settings.form.fields.password'))
                    ->password()
                    ->revealable()
                    // TODO: fix this when Filament has fixed this
                    ->required(fn (Operation|string $operation): bool => ($operation instanceof Operation ? $operation : Operation::from($operation)) === Operation::Create)
                    ->visible(fn (Operation|string $operation): bool => ($operation instanceof Operation ? $operation : Operation::from($operation)) === Operation::Create)
                    ->helperText(__('admin.mailer_settings.form.fields.password_helper')),
                TextInput::make('from_address')
                    ->label(__('admin.mailer_settings.form.fields.from_address'))
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('from_name')
                    ->label(__('admin.mailer_settings.form.fields.from_name'))
                    ->maxLength(255),
                TextInput::make('reply_to_address')
                    ->label(__('admin.mailer_settings.form.fields.reply_to_address'))
                    ->email()
                    ->maxLength(255),
            ]);
    }
}
