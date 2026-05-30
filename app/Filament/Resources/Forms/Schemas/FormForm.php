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
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
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
                    RichEditor::make('description')
                        ->label(__('admin.forms.form.fields.description'))
                        ->toolbarButtons([
                            ['bold', 'italic', 'underline', 'link'],
                            ['h1', 'h2', 'paragraph'],
                            ['bulletList', 'orderedList'],
                            ['undo', 'redo'],
                        ])
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
                                ->icon(Heroicon::Cog6Tooth)
                                ->badge(function (array $arguments, Repeater $component, $record): ?int {
                                    // Note: when adding a field, the $record param gets passed a FormSection.
                                    // I think this is a Filament issue, but for now this fixes it.
                                    if (! $record instanceof FormField) {
                                        return null;
                                    }

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
                                ->visible(fn ($record): bool => $record instanceof FormField) // Since using this action on unsaved records triggers a Filament bug
                                ->record(fn (array $arguments, Repeater $component): ?FormField => self::getFieldFromArgsAndRepeater($arguments, $component))
                                ->fillForm(fn (FormSection|FormField|null $record): array => $record instanceof FormField ? [
                                    'dependency_field_id' => $record->dependency_field_id,
                                    'dependency_option_id' => $record->dependency_option_id,
                                    'dependency_equals' => $record->dependency_equals,
                                    'default_option_id' => $record->default_option_id,
                                    'hide_option_price' => $record->hide_option_price,
                                    'options' => $record->options()->get()->toArray(),
                                ] : [])
                                ->action(function (array $data, FormField $record) {
                                    $record->fill([
                                        'dependency_field_id' => Arr::get($data, 'dependency_field_id'),
                                        'dependency_option_id' => Arr::get($data, 'dependency_option_id'),
                                        'dependency_equals' => Arr::get($data, 'dependency_equals'),
                                    ]);

                                    if (in_array($record->type, [FormFieldType::Radio, FormFieldType::Select], true)) {
                                        $record->default_option_id = Arr::get($data, 'default_option_id');
                                        $record->hide_option_price = Arr::get($data, 'hide_option_price');
                                    }

                                    $record->save();
                                })
                                ->disabled(fn (EditEvent|ViewEvent $livewire): bool => ! $livewire instanceof EditEvent)
                                ->schema([
                                    Fieldset::make(__('admin.forms.form.section.conditional_visibility'))
                                        ->columns(5)
                                        ->schema([
                                            Select::make('dependency_field_id')
                                                ->label(__('admin.forms.form.field.fields.visibility_field'))
                                                ->columnSpan(2)
                                                ->live()
                                                ->options(function (?FormField $record): array {
                                                    $form = $record?->section?->form;
                                                    if ($form === null) {
                                                        return [];
                                                    }

                                                    return $form->fields()
                                                        ->whereIn('type', [FormFieldType::Radio, FormFieldType::Select])
                                                        ->when($record->exists, fn (Builder $query): Builder => $query->whereNot('id', $record->id))
                                                        ->pluck('label', 'id')
                                                        ->toArray();
                                                })
                                                ->nullable()
                                                ->searchable(),
                                            Select::make('dependency_equals')
                                                ->label(__('admin.forms.form.field.fields.visibility_condition'))
                                                ->requiredWith('dependency_field_id')
                                                ->boolean(trueLabel: __('admin.forms.form.field.fields.equals'), falseLabel: __('admin.forms.form.field.fields.not_equals'))
                                                ->default(true)
                                                ->visible(fn (Get $get): bool => $get('dependency_field_id') !== null),
                                            Select::make('dependency_option_id')
                                                ->requiredWith('dependency_field_id')
                                                ->label(__('admin.forms.form.field.fields.visibility_option'))
                                                ->columnSpan(2)
                                                ->options(fn (?FormField $field, Get $get): ?array => $field?->section->form
                                                    ->fields()->where('id', $get('dependency_field_id'))->first()
                                                    ?->options()->pluck('label', 'id')->toArray()
                                                )
                                                ->nullable()
                                                ->visible(fn (Get $get): bool => $get('dependency_field_id') !== null),
                                        ]),

                                    Fieldset::make(__('admin.forms.form.section.options'))
                                        ->visible(fn (FormField|FormSection|null $record): bool => $record instanceof FormField && $record->type?->hasOptions() ?? false)
                                        ->columns(1)
                                        ->schema([
                                            Select::make('default_option_id')
                                                ->label(__('admin.forms.form.field.fields.default_option'))
                                                ->options(fn (?FormField $record): array => $record instanceof FormField
                                                    ? $record->options()->pluck('label', 'id')->toArray()
                                                    : [])
                                                ->nullable()
                                                ->searchable()
                                                ->visible(fn (?FormField $record): bool => $record?->type === FormFieldType::Select),
                                            Toggle::make('hide_option_price')
                                                ->label(__('admin.forms.form.field.fields.hide_option_price'))
                                                ->default(false),
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
                                                ->columns(1),
                                        ]),
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
