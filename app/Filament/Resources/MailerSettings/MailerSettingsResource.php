<?php

namespace App\Filament\Resources\MailerSettings;

use App\Filament\Resources\MailerSettings\Pages\CreateMailerSettings;
use App\Filament\Resources\MailerSettings\Pages\EditMailerSettings;
use App\Filament\Resources\MailerSettings\Pages\ListMailerSettings;
use App\Filament\Resources\MailerSettings\Schemas\MailerSettingsForm;
use App\Filament\Resources\MailerSettings\Tables\MailerSettingsTable;
use App\Models\MailerSettings;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MailerSettingsResource extends Resource
{
    protected static ?string $model = MailerSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    public static function getNavigationLabel(): string
    {
        return __('admin.mailer_settings.resource.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.mailer_settings.resource.singular_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.mailer_settings.resource.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return MailerSettingsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MailerSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMailerSettings::route('/'),
            'create' => CreateMailerSettings::route('/create'),
            'edit' => EditMailerSettings::route('/{record}/edit'),
        ];
    }
}
