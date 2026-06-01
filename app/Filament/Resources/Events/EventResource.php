<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\Pages\ManageEventInvites;
use App\Filament\Resources\Events\Pages\ManageEventRegistrations;
use App\Filament\Resources\Events\Pages\ViewEvent;
use App\Filament\Resources\Events\Schemas\EventForm;
use App\Filament\Resources\Events\Tables\EventsTable;
use App\Models\Event;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Calendar;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 1;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationLabel(): string
    {
        return __('admin.events.resource.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.events.resource.singular_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.events.resource.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return EventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventsTable::configure($table);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        $viewOrEdit = Auth::user()->can('update', $page->getRecord()) ? EditEvent::class : ViewEvent::class;

        return $page->generateNavigationItems([
            $viewOrEdit,
            ManageEventRegistrations::class,
            ManageEventInvites::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'edit' => EditEvent::route('/{record}/edit'),
            'view' => ViewEvent::route('/{record}/view'),
            'registrations' => ManageEventRegistrations::route('/{record}/registrations'),
            'invites' => ManageEventInvites::route('/{record}/invites'),
        ];
    }
}
