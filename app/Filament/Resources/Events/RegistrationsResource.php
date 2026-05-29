<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\RegistrationsResource\Pages\EditRegistration;
use App\Filament\Resources\Registrations\Schemas\RegistrationForm;
use App\Models\Registration;
use Filament\Resources\ParentResourceRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;

class RegistrationsResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $slug = 'registrations';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return __('admin.registrations.resource.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.registrations.resource.singular_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.registrations.resource.plural_label');
    }

    public static function getParentResourceRegistration(): ?ParentResourceRegistration
    {
        return EventResource::asParent(childResource: static::class)
            ->relationship('registrations')
            ->inverseRelationship('event');
    }

    public static function form(Schema $schema): Schema
    {
        return RegistrationForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'edit' => EditRegistration::route('/{record}/edit'),
        ];
    }
}
