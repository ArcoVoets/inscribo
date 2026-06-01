<?php

namespace App\Filament\Resources\ApiKeys\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

class ApiKeyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.api_keys.fields.name'))
                    ->required(),
                TextInput::make('key')
                    ->label(__('admin.api_keys.fields.key'))
                    ->password()
                    ->required()
                    ->visibleOn(Operation::Create),
            ]);
    }
}
