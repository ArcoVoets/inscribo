<?php

namespace App\Enums;

enum FormFieldType: string
{
    case Text = 'text';
    case LongText = 'long_text';
    case Email = 'email';
    case Radio = 'radio';
    case Select = 'select';
    case Date = 'date';

    public function getLabel(): string
    {
        return match ($this) {
            self::Text => __('admin.forms.field_types.text'),
            self::LongText => __('admin.forms.field_types.long_text'),
            self::Email => __('admin.forms.field_types.email'),
            self::Radio => __('admin.forms.field_types.radio'),
            self::Select => __('admin.forms.field_types.select'),
            self::Date => __('admin.forms.field_types.date'),
        };
    }

    public function hasOptions(): bool
    {
        return match ($this) {
            self::Radio, self::Select => true,
            default => false,
        };
    }

    /**
     * Whether the field value should be escaped when exporting to CSV.
     * This is the case when the field may contain special characters, like commas,
     * quotes or newlines.
     */
    public function shouldBeEscaped(): bool
    {
        return match ($this) {
            self::Text, self::LongText, self::Email => true,
            self::Radio, self::Select, self::Date => false,
        };
    }
}
