<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.users.form.fields.name'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('admin.users.form.fields.email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label(__('admin.users.form.fields.password'))
                    ->password()
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->extraInputAttributes(['autocomplete' => 'new-password'])
                    ->helperText(__('admin.users.form.fields.password_helper')),
                Select::make('roles')
                    ->label(__('admin.users.form.fields.roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                TextEntry::make('email_verified_at')
                    ->label(__('admin.users.form.fields.email_verified_at')),
            ]);
    }
}
