<?php

namespace App\Filament\Resources\Events\Pages;

use App\Actions\ExportRegistrations;
use App\Enums\RegistrationStates;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\RegistrationsResource;
use App\Filament\Resources\Registrations\Schemas\RegistrationForm;
use App\Filament\Resources\Registrations\Tables\RegistrationsTable;
use App\Models\Event;
use App\Models\RegistrationState;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Override;

class ManageEventRegistrations extends ManageRelatedRecords
{
    protected static string $resource = EventResource::class;

    protected static string $relationship = 'registrations';

    protected static ?string $relatedResource = RegistrationsResource::class;

    public static function getNavigationIcon(): Heroicon
    {
        return Heroicon::OutlinedTicket;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.events.pages.manage_registrations.navigation_label');
    }

    public function getTitle(): string
    {
        return __('admin.events.pages.manage_registrations.title');
    }

    public function getTabs(): array
    {
        $tabs = [];

        /** @var Event $event */
        $event = $this->getOwnerRecord()
            ->loadCount('registrations');

        $registrationStateCounts = DB::table('registrations')
            ->selectRaw('count(*) as total')
            ->where('event_id', $event->id)
            ->selectSub(
                RegistrationState::query()
                    ->selectRaw(
                        'case when (type = ? and expires_at is not null and expires_at < ?) then ? else type end',
                        [
                            RegistrationStates::PaymentPending->value,
                            now(),
                            RegistrationStates::PaymentExpired->value,
                        ]
                    )
                    ->whereColumn('registration_id', 'registrations.id')
                    ->latest('id')
                    ->limit(1),
                'current_status')
            ->groupBy('current_status')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->current_status => $item->total]);

        $tabs['all'] = Tab::make(__('admin.events.pages.manage_registrations.all'))
            ->badge($event->registrations_count);

        foreach (RegistrationStates::tabsOrder() as $state) {
            $tabs[$state->value] = Tab::make($state->getLabel())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereState($state))
                ->badge($registrationStateCounts->get($state->value, 0));
        }

        return $tabs;
    }

    public function form(Schema $schema): Schema
    {
        return RegistrationForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return RegistrationsTable::configure($table);
    }

    #[Override]
    public function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label(__('admin.events.pages.manage_registrations.export.title'))
                ->schema([
                    // Select separator, states to include and whether to include headers in the export
                    Select::make('separator')
                        ->label(__('admin.events.pages.manage_registrations.export.separator'))
                        ->options([
                            ',' => __('admin.events.pages.manage_registrations.export.separator_comma'),
                            ';' => __('admin.events.pages.manage_registrations.export.separator_semicolon'),
                            "\t" => __('admin.events.pages.manage_registrations.export.separator_tab'),
                        ])
                        ->default(',')
                        ->required(),
                    Toggle::make('include_headers')
                        ->label(__('admin.events.pages.manage_registrations.export.include_headers'))
                        ->required()
                        ->default(true),
                    CheckboxList::make('states')
                        ->required()
                        ->columns(2)
                        ->label(__('admin.events.pages.manage_registrations.export.states'))
                        ->options(RegistrationStates::class)
                        ->default([RegistrationStates::Registered])
                        ->columnSpanFull(),
                    DateTimePicker::make('from_date')
                        // Even though the timezone this is also set in the AppServiceProvider, we also set it here, since that fixes the
                        // default time being off because of timezones. Not sure why, but it fixes the issue.
                        ->timezone(config('app.timezone'))
                        ->default(fn (Event $record): CarbonInterface => $record->created_at->startOfDay())
                        ->label(__('admin.events.pages.manage_registrations.export.from_date'))
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    /** @var Event $event */
                    $event = $this->getOwnerRecord()->load('registrations');
                    $registrations = $event->registrations()->whereStateIn($data['states'])->where('created_at', '>=', $data['from_date'])->get();

                    $csv = app()->make(ExportRegistrations::class)->execute($event, $registrations, $data['separator'], $data['include_headers']);
                    $filename = __('admin.events.pages.manage_registrations.export.file_name', [
                        'event_title' => $event->cleanTitleForFilename(),
                        'event_id' => $event->id,
                        'timestamp' => now()->format('Ymd_His'),
                    ]).'.csv';

                    return response()->streamDownload(function () use ($csv) {
                        echo $csv;
                    }, $filename, [
                        'Content-Type' => 'text/csv',
                    ]);
                }),
        ];
    }
}
