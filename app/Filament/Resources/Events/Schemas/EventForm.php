<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ViewEvent;
use App\Filament\Resources\Forms\Schemas\FormForm;
use App\Models\Event;
use App\Models\Form;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\UnorderedList;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make(__('admin.events.form.warnings.heading'))
                    ->warning()
                    ->description(fn (EditEvent|CreateEvent|ViewEvent $livewire): HtmlString => $livewire->getEventSetupWarningsDescription())
                    ->visible(fn (EditEvent|CreateEvent|ViewEvent $livewire): bool => $livewire->hasEventSetupWarnings())
                    ->columnSpanFull(),
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make(__('admin.events.form.tabs.general'))
                            ->icon(Heroicon::InformationCircle)
                            ->badge(fn (EditEvent|CreateEvent|ViewEvent $livewire): ?int => $livewire->getEventSetupWarningCountForTab('general'))
                            ->badgeColor(fn (EditEvent|CreateEvent|ViewEvent $livewire): ?string => $livewire->getEventSetupWarningCountForTab('general') ? 'warning' : null)
                            ->badgeIcon(fn (EditEvent|CreateEvent|ViewEvent $livewire): ?Heroicon => $livewire->getEventSetupWarningCountForTab('general') ? Heroicon::ExclamationTriangle : null)
                            ->columns(2)
                            ->schema(fn (Schema $schema): Schema => self::generalTab($schema)),
                        Tab::make(__('admin.events.form.tabs.wordpress'))
                            ->icon(Heroicon::ArrowTopRightOnSquare)
                            ->visible(fn (Get $get): bool => (bool) $get('wordpress_enabled'))
                            ->schema(fn (Schema $schema): Schema => self::wordpressTab($schema)),
                        Tab::make(__('admin.events.form.tabs.form'))
                            ->icon(Heroicon::DocumentText)
                            ->badge(fn (EditEvent|CreateEvent|ViewEvent $livewire): ?int => $livewire->getEventSetupWarningCountForTab('form'))
                            ->badgeColor(fn (EditEvent|CreateEvent|ViewEvent $livewire): ?string => $livewire->getEventSetupWarningCountForTab('form') ? 'warning' : null)
                            ->badgeIcon(fn (EditEvent|CreateEvent|ViewEvent $livewire): ?Heroicon => $livewire->getEventSetupWarningCountForTab('form') ? Heroicon::ExclamationTriangle : null)
                            ->schema(fn (Schema $schema): Schema => self::formTab($schema))
                            ->hiddenOn(Operation::Create),
                        Tab::make(__('admin.events.form.tabs.emails'))
                            ->icon(Heroicon::Envelope)
                            ->badge(fn (EditEvent|CreateEvent|ViewEvent $livewire): ?int => $livewire->getEventSetupWarningCountForTab('emails'))
                            ->badgeColor(fn (EditEvent|CreateEvent|ViewEvent $livewire): ?string => $livewire->getEventSetupWarningCountForTab('emails') ? 'warning' : null)
                            ->badgeIcon(fn (EditEvent|CreateEvent|ViewEvent $livewire): ?Heroicon => $livewire->getEventSetupWarningCountForTab('emails') ? Heroicon::ExclamationTriangle : null)
                            ->schema(fn (Schema $schema): Schema => self::emailTab($schema))
                            ->hiddenOn(Operation::Create),
                    ])
                    ->columnSpanFull()
                    ->contained(false)
                    ->persistTabInQueryString(),
            ]);
    }

    protected static function generalTab(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.events.form.sections.event'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label(__('admin.events.form.fields.title'))
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('capacity')
                            ->label(__('admin.events.form.fields.capacity'))
                            ->required()
                            ->numeric()
                            ->integer()
                            ->columnSpanFull()
                            ->minValue(1),
                        Toggle::make('show_waitlist_position')
                            ->label(__('admin.events.form.fields.show_waitlist_position'))
                            ->default(false)
                            ->inline(false)
                            ->helperText(__('admin.events.form.fields.show_waitlist_position_helper')),
                        Toggle::make('show_capacity_data')
                            ->label(__('admin.events.form.fields.show_capacity_data'))
                            ->default(false)
                            ->inline(false)
                            ->helperText(__('admin.events.form.fields.show_capacity_data_helper')),
                        TextInput::make('year')
                            ->columnSpanFull()
                            ->label(__('admin.events.form.fields.year'))
                            ->helperText(__('admin.events.form.fields.year_helper')),
                    ]),

                Section::make(__('admin.events.form.sections.registration'))
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('opens_at')
                            ->label(__('admin.events.form.fields.opens_at'))
                            ->required(),
                        DateTimePicker::make('closes_at')
                            ->label(__('admin.events.form.fields.closes_at'))
                            ->after('opens_at'),
                        TextInput::make('registration_link')
                            ->label(__('admin.events.form.fields.registration_link'))
                            ->visible(fn (?Event $record): bool => $record !== null)
                            ->formatStateUsing(fn (?Event $record): ?string => $record?->registrationUrl())
                            ->dehydrated(false)
                            ->disabled()
                            ->copyable()
                            ->columnSpanFull(),
                        Select::make('registration_expiration_minutes')
                            ->label(__('admin.events.form.fields.registration_expiration_minutes'))
                            ->options([
                                15 => __('admin.events.form.options.registration_expiration_minutes', ['minutes' => 15]),
                                30 => __('admin.events.form.options.registration_expiration_minutes', ['minutes' => 30]),
                                60 => __('admin.events.form.options.registration_expiration_minutes', ['minutes' => 60]),
                                2 * 60 => __('admin.events.form.options.registration_expiration_hours', ['hours' => 2]),
                                24 * 60 => __('admin.events.form.options.registration_expiration_hours', ['hours' => 24]),
                            ])
                            ->required()
                            ->helperText(__('admin.events.form.fields.registration_expiration_minutes_helper'))
                            ->default(30),
                    ]),
                Section::make(__('admin.events.form.sections.other'))
                    ->schema([
                        Toggle::make('wordpress_enabled')
                            ->label(__('admin.events.form.fields.wordpress_enabled'))
                            ->default(false)
                            ->inline(false)
                            ->disabled(fn (?Event $record): bool => $record?->registrationsExist() ?? false)
                            ->helperText(fn (?Event $record): ?string => $record?->registrationsExist()
                                    ? __('admin.events.form.fields.wordpress_locked_helper')
                                    : null
                            )
                            ->live(),
                        TextInput::make('home_url')
                            ->label(__('admin.events.form.fields.home_url'))
                            ->url()
                            ->rules(['url:https,http'])
                            ->helperText(__('admin.events.form.fields.home_url_helper')),
                    ]),

                Section::make(__('admin.events.form.sections.accent_colors'))
                    ->columns(2)
                    ->schema([
                        ColorPicker::make('accent_color_title_and_button')
                            ->label(__('admin.events.form.fields.accent_color_title_and_button'))
                            ->helperText(__('admin.events.form.fields.accent_color_title_and_button_help_text')),
                        ColorPicker::make('accent_color_required_and_hover')
                            ->label(__('admin.events.form.fields.accent_color_required_and_hover'))
                            ->helperText(__('admin.events.form.fields.accent_color_required_and_hover_help_text')),
                        ColorPicker::make('accent_color_label_and_radio')
                            ->label(__('admin.events.form.fields.accent_color_label_and_radio'))
                            ->helperText(__('admin.events.form.fields.accent_color_label_and_radio_help_text')),
                        ColorPicker::make('accent_color_section_title')
                            ->label(__('admin.events.form.fields.accent_color_section_title'))
                            ->helperText(__('admin.events.form.fields.accent_color_section_title_help_text')),
                    ]),

                Section::make(__('admin.events.form.sections.payment'))
                    ->schema([
                        Select::make('api_key_id')
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->label(__('admin.events.form.fields.api_key'))
                            ->relationship('apiKey', 'name'),
                        Text::make(__('admin.events.form.fields.payment_description_helper')),
                        UnorderedList::make(Event::paymentDescriptionMergeTags()),
                        Text::make(__('admin.events.form.fields.payment_description_form_fields_merge_tags_helper')),
                        UnorderedList::make(fn (?Event $record): ?array => $record?->formFieldsMergeTags()),
                        TextInput::make('payment_description_template')
                            ->label(__('admin.events.form.fields.payment_description_template')),
                    ]),
            ]);
    }

    protected static function wordpressTab(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.events.form.sections.wordpress'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('wordpress_form_page_url')
                            ->label(__('admin.events.form.fields.wordpress_form_page_url'))
                            ->columnSpan(2)
                            ->activeUrl()
                            ->url()
                            ->rules(['url:https,http'])
                            ->placeholder('https://example.com/registration-form')
                            ->required(fn (Get $get): bool => (bool) $get('wordpress_enabled'))
                            ->disabled(fn (?Event $record): bool => $record?->invitesExist() ?? false)
                            ->helperText(function (?Event $record): string {
                                if ($record?->invitesExist()) {
                                    return __('admin.events.form.fields.wordpress_locked_helper');
                                }

                                return __('admin.events.form.fields.wordpress_form_page_url_helper');
                            }),
                        TextInput::make('wordpress_status_page_url')
                            ->label(__('admin.events.form.fields.wordpress_status_page_url'))
                            ->columnSpan(2)
                            ->activeUrl()
                            ->url()
                            ->rules(['url:https,http'])
                            ->placeholder('https://example.com/registration-status')
                            ->required(fn (Get $get): bool => (bool) $get('wordpress_enabled'))
                            ->disabled(fn (?Event $record): bool => $record?->registrationsExist() ?? false)
                            ->helperText(function (?Event $record): string {
                                if ($record?->registrationsExist()) {
                                    return __('admin.events.form.fields.wordpress_locked_helper');
                                }

                                return __('admin.events.form.fields.wordpress_status_page_url_helper');
                            }),
                    ]),
                Section::make(__('admin.events.form.sections.wordpress_embed'))
                    ->columns(2)
                    ->visible(fn (?Event $record): bool => self::shouldShowWordpressInstructions($record))
                    ->schema([
                        Callout::make(__('admin.events.form.wordpress_embed.instructions_heading'))
                            ->icon(Heroicon::InformationCircle)
                            ->description(fn (?Event $record): ?HtmlString => $record ? self::wordpressInstructionsDescription() : null)
                            ->actions([
                                Action::make('open_plugin_page')
                                    ->link()
                                    ->icon(Heroicon::ArrowTopRightOnSquare)
                                    ->iconPosition(IconPosition::After)
                                    ->label(__('admin.events.form.wordpress_embed.plugin_page_action'))
                                    ->url('https://wordpress.org/plugins/inscribo-embed/')
                                    ->openUrlInNewTab(),
                            ])
                            ->columnSpanFull(),
                        TextInput::make('wordpress_form_shortcode')
                            ->label(__('admin.events.form.wordpress_embed.form_shortcode_label'))
                            ->formatStateUsing(fn (?Event $record): ?string => $record?->wordpressFormShortcode())
                            ->dehydrated(false)
                            ->disabled()
                            ->copyable()
                            ->columnSpanFull(),
                        TextInput::make('wordpress_status_shortcode')
                            ->label(__('admin.events.form.wordpress_embed.status_shortcode_label'))
                            ->formatStateUsing(fn (?Event $record): ?string => $record?->wordpressStatusShortcode())
                            ->dehydrated(false)
                            ->disabled()
                            ->copyable()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (?Event $record): bool => $record !== null),
            ]);
    }

    protected static function formTab(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.events.form.fields.form_link_label'))
                    ->columnSpanFull()
                    ->relationship('form')
                    ->schema(fn (Schema $schema) => FormForm::configure($schema))
                    ->headerActions([
                        Action::make('open_preview')
                            ->link()
                            ->icon(Heroicon::ArrowTopRightOnSquare)
                            ->iconPosition(IconPosition::After)
                            ->label(__('admin.events.form.actions.open_preview'))
                            ->url(fn (?Form $record): ?string => $record ? route('events.preview', $record->event) : null)
                            ->openUrlInNewTab()
                            ->visible(fn (?Form $record): bool => $record !== null),
                    ]),
            ]);
    }

    protected static function emailTab(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.events.form.sections.mailer_settings'))
                    ->schema([
                        Select::make('mailer_settings_id')
                            ->label(__('admin.events.form.fields.mailer_settings'))
                            ->relationship('mailerSettings', 'from_address')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText(__('admin.events.form.fields.mailer_settings_helper'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Repeater::make('mailTemplates')
                    ->label(__('admin.events.form.sections.emails'))
                    ->relationship()
                    ->schema([
                        TextInput::make('subject')
                            ->label(__('admin.events.form.fields.mail_template_subject'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->label(__('admin.events.form.fields.mail_template_content'))
                            ->required()
                            ->json()
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'link'],
                                ['h1', 'h2', 'paragraph', 'small'],
                                ['bulletList', 'orderedList'],
                                ['undo', 'redo'],
                            ])
                            ->mergeTags(fn (RichEditor $component): array => $component->getRecord()->type->allMergeTagLabels())
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->addable(false)
                    ->deletable(false)
                    ->collapsible()
                    ->collapsed(true)
                    ->itemLabel(fn (Schema $item): string => $item->getRecord()->type->getLabel()),
            ]);
    }

    private static function shouldShowWordpressInstructions(?Event $record): bool
    {
        return $record !== null
            && $record->wordpress_enabled
            && filled($record->wordpress_form_page_url)
            && filled($record->wordpress_status_page_url);
    }

    private static function wordpressInstructionsDescription(): HtmlString
    {
        $steps = [
            __('admin.events.form.wordpress_embed.steps.install_plugin', [
                'plugin' => __('admin.events.form.wordpress_embed.plugin_name'),
            ]),
            __('admin.events.form.wordpress_embed.steps.create_form_page'),
            __('admin.events.form.wordpress_embed.steps.paste_form_shortcode'),
            __('admin.events.form.wordpress_embed.steps.publish_form_page'),
            __('admin.events.form.wordpress_embed.steps.create_status_page'),
            __('admin.events.form.wordpress_embed.steps.paste_status_shortcode'),
            __('admin.events.form.wordpress_embed.steps.publish_status_page'),
        ];

        $listItems = collect($steps)
            ->map(fn (string $step): string => '<li>'.e($step).'</li>')
            ->implode('');

        $intro = e(__('admin.events.form.wordpress_embed.intro'));

        return new HtmlString('<p class="mb-2">'.$intro.'</p><ol class="list-decimal space-y-1 ps-6">'.$listItems.'</ol>');
    }
}
