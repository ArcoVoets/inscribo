<?php

namespace App\Filament\Resources\Forms\Schemas;

use App\Enums\FormFieldType;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ViewEvent;
use App\Models\Event;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSection;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rules\Unique;

class FormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make()
                ->columnSpanFull()
                ->schema([
                    TextInput::make('title')
                        ->label(__('admin.forms.form.fields.title'))
                        ->required(),
                    TextInput::make('base_price_cents')
                        ->label(__('admin.forms.form.fields.base_price'))
                        ->prefix('€')
                        ->numeric()
                        ->inputMode('decimal')
                        ->step('0.01')
                        ->minValue(0)
                        ->dehydrateStateUsing(function ($state): ?int {
                            if ($state === null || $state === '') {
                                return 0;
                            }

                            return (int) round(((float) $state) * 100);
                        })
                        ->formatStateUsing(fn ($state): ?string => $state === null ? null : (int) $state / 100),
                    Textarea::make('description')
                        ->label(__('admin.forms.form.fields.description'))
                        ->columnSpanFull(),
                    Select::make('email_field_id')
                        ->label(__('admin.forms.form.fields.email_field'))
                        ->options(function (EditEvent|ViewEvent $livewire) {
                            if (! $livewire instanceof EditEvent) {
                                return [];
                            }

                            $form = $livewire->getRecord()->form;

                            if ($form === null) {
                                return [];
                            }

                            return $form->fields()
                                ->where('type', FormFieldType::Email)
                                ->pluck('label', 'id')
                                ->toArray();
                        })
                        ->nullable()
                        ->helperText(__('admin.forms.form.fields.email_field_help_text')),
                    Select::make('name_field_id')
                        ->label(__('admin.forms.form.fields.name_field'))
                        ->options(function (EditEvent|ViewEvent $livewire) {
                            if (! $livewire instanceof EditEvent) {
                                return [];
                            }

                            $form = $livewire->getRecord()->form;

                            if ($form === null) {
                                return [];
                            }

                            return $form->fields()
                                ->where('type', FormFieldType::Text)
                                ->pluck('label', 'id')
                                ->toArray();
                        })
                        ->nullable()
                        ->helperText(__('admin.forms.form.fields.name_field_help_text')),
                ]),

            Repeater::make('sections')
                ->label(__('admin.forms.form.sections'))
                ->orderColumn('sort_order')
                ->relationship()
                ->deletable()
                ->deleteAction(function (Action $action): void {
                    $action->disabled(function (array $arguments, Repeater $component, Form $record): bool {
                        $sectionId = $component->getRawItemState($arguments['item'])['id'] ?? null;
                        if ($sectionId === null) {
                            return false;
                        }

                        $section = $record->sections->firstWhere('id', $sectionId);
                        if ($section === null) {
                            return false;
                        }

                        return $section->fieldsWithRegistrationsExist();
                    })
                        ->tooltip(function (array $arguments, Repeater $component, Form $record): ?string {
                            $sectionId = $component->getRawItemState($arguments['item'])['id'] ?? null;
                            if ($sectionId === null) {
                                return null;
                            }

                            $section = $record->sections->firstWhere('id', $sectionId);
                            if ($section === null) {
                                return null;
                            }
                            if (! $section->fieldsWithRegistrationsExist()) {
                                return null;
                            }

                            return __('admin.forms.form.section.delete_disabled_tooltip');
                        });
                })
                ->collapsible()
                ->addActionLabel(__('admin.forms.form.section.add_label'))
                ->schema([
                    TextInput::make('title')->required()->label(__('admin.forms.form.section.fields.title')),
                    Repeater::make('fields')
                        ->label(__('admin.forms.form.section.fields.list'))
                        ->addActionLabel(__('admin.forms.form.field.add_label'))
                        ->relationship()
                        ->orderColumn('sort_order')
                        ->table([
                            TableColumn::make(__('admin.forms.form.field.fields.label'))->markAsRequired(),
                            TableColumn::make(__('admin.forms.form.field.fields.name'))->markAsRequired(),
                            TableColumn::make(__('admin.forms.form.field.fields.type'))->markAsRequired(),
                            TableColumn::make(__('admin.forms.form.field.fields.width'))->markAsRequired(),
                            TableColumn::make(__('admin.forms.form.field.fields.required'))->markAsRequired(),
                        ])
                        ->compact()
                        ->schema([
                            TextInput::make('label')->required()->label(__('admin.forms.form.field.fields.label')),
                            TextInput::make('name')
                                ->required()
                                ->label(__('admin.forms.form.field.fields.name'))
                                ->unique('form_fields',
                                    modifyRuleUsing: fn (Unique $rule, EditEvent|ViewEvent $livewire): Unique => $rule->where('form_id', $livewire->getRecord()->form->id))
                                ->regex('/^[a-zA-Z0-9-_]+$/')
                                ->validationMessages([
                                    'regex' => __('admin.forms.form.field.option_value_validation_message'),
                                ]),
                            Select::make('type')->options(FormFieldType::class)->required(),
                            TextInput::make('width')->label(__('admin.forms.form.field.fields.width'))->integer()->minValue(1)->maxValue(100)->default(100),
                            Toggle::make('required')->label(__('admin.forms.form.field.fields.required'))->default(true),
                        ])
                        ->deletable()
                        ->deleteAction(function (Action $action): void {
                            $action->disabled(fn (array $arguments, Repeater $component, FormSection|Event $record): bool => $record instanceof FormSection && $record
                                ->registrationValuesExistForField($component->getRawItemState($arguments['item'])['id'] ?? null)
                            )
                                ->tooltip(function (array $arguments, Repeater $component, FormSection|Event $record): ?string {
                                    return ($record instanceof FormSection && $record->registrationValuesExistForField($component->getRawItemState($arguments['item'])['id'] ?? null))
                                        ? __('admin.forms.form.field.delete_disabled_tooltip')
                                        : null;
                                });
                        })
                        ->columns(1)
                        ->extraItemActions([
                            Action::make('set_options')
                                ->icon(Heroicon::ListBullet)
                                ->badge(function (array $arguments, Repeater $component, FormField $record): ?int {
                                    $fields = $component->getCachedExistingRecords()->ensure(FormField::class);
                                    if ($record->getAttributeValue('options_count') === null) {
                                        $fields->loadCount('options');
                                    }

                                    $field = $fields->firstWhere('id', $component->getRawItemState($arguments['item'])['id'] ?? null);
                                    if ($field === null || $field->type === null || ! $field->type->hasOptions()) {
                                        return null;
                                    }

                                    return $record->options_count;
                                })
                                ->visible(function (array $arguments, Repeater $component): bool {
                                    $fields = $component->getCachedExistingRecords()->ensure(FormField::class);
                                    $field = $fields->firstWhere('id', $component->getRawItemState($arguments['item'])['id'] ?? null);

                                    return $field !== null && $field->type !== null & $field->type->hasOptions();
                                })
                                ->record(fn (array $arguments, Repeater $component): ?FormField => self::getFieldFromArgsAndRepeater($arguments, $component))
                                ->fillForm(fn ($record): array => $record === null ? [] : [
                                    'default_option_id' => $record->default_option_id,
                                    'hide_option_price' => $record->hide_option_price,
                                    'options' => $record->options()->get()->toArray(),
                                ])
                                ->disabled(fn (EditEvent|ViewEvent $livewire): bool => ! $livewire instanceof EditEvent)
                                ->schema([
                                    Select::make('default_option_id')
                                        ->label(__('admin.forms.form.field.fields.default_option'))
                                        ->options(fn (?FormField $record): array => $record
                                            ? $record->options()->pluck('label', 'id')->toArray()
                                            : [])
                                        ->nullable()
                                        ->searchable()
                                        ->visible(fn (?FormField $record): bool => $record?->type === FormFieldType::Select),
                                    Toggle::make('hide_option_price')
                                        ->label(__('admin.forms.form.field.fields.hide_option_price'))
                                        ->default(false)
                                        ->visible(fn (?FormField $record): bool => $record?->type?->hasOptions() ?? false),
                                    Repeater::make('options')
                                        ->addActionLabel(__('admin.forms.form.field.add_option_label'))
                                        ->label(__('admin.forms.form.field.fields.options'))
                                        ->table([
                                            TableColumn::make(__('admin.forms.form.option.fields.label'))->markAsRequired(),
                                            TableColumn::make(__('admin.forms.form.option.fields.value'))->markAsRequired(),
                                            TableColumn::make(__('admin.forms.form.option.fields.price')),
                                        ])
                                        ->orderColumn('sort_order')
                                        ->compact()
                                        ->relationship('options')
                                        ->schema([
                                            TextInput::make('label')->required()->label(__('admin.forms.form.option.fields.label')),
                                            TextInput::make('value')
                                                ->required()
                                                ->label(__('admin.forms.form.option.fields.value'))
                                                ->unique('form_field_options',
                                                    modifyRuleUsing: fn (Unique $rule, TextInput $component): Unique => $rule->where('field_id', $component->getContainer()->getParentComponent()->getRecord()->id)
                                                )
                                                ->regex('/^[a-zA-Z0-9-_]+$/')
                                                ->validationMessages([
                                                    'regex' => __('admin.forms.form.field.option_value_validation_message'),
                                                ]),
                                            TextInput::make('price_cents')
                                                ->label(__('admin.forms.form.option.fields.price'))
                                                ->prefix('€')
                                                ->numeric()
                                                ->default(0)
                                                ->inputMode('decimal')
                                                ->step('0.01')
                                                ->dehydrateStateUsing(function ($state): ?int {
                                                    if ($state === null || $state === '') {
                                                        return 0;
                                                    }

                                                    return (int) round(((float) $state) * 100);
                                                })
                                                ->formatStateUsing(fn ($state): ?string => $state === null ? null : (int) $state / 100),
                                        ])
                                        ->columns(1)
                                        ->visible(fn (?FormField $record): bool => $record?->type?->hasOptions() ?? false),
                                ]),
                        ]),
                ])
                ->columns(1)
                ->columnSpanFull(),
        ]);
    }

    private static function getFieldFromArgsAndRepeater(array $arguments, Repeater $component): ?FormField
    {
        $fieldId = $component->getRawItemState($arguments['item'])['id'] ?? null;
        if ($fieldId === null) {
            return null;
        }

        return $component->getCachedExistingRecords()->ensure(FormField::class)->firstWhere('id', $fieldId);
    }
}
