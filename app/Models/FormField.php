<?php

namespace App\Models;

use App\Enums\FormFieldType;
use App\Observers\FormFieldObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(FormFieldObserver::class)]
class FormField extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => FormFieldType::class,
            'default_option_id' => 'integer',
            'hide_option_price' => 'boolean',
            'width' => 'integer',
            'required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected $attributes = [
        'sort_order' => 0,
        'required' => true,
        'hide_option_price' => false,
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(FormSection::class, 'section_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(FormFieldOption::class, 'field_id')->orderBy('sort_order');
    }

    public function defaultOption(): BelongsTo
    {
        return $this->belongsTo(FormFieldOption::class, 'default_option_id');
    }

    public function registrationValues(): HasMany
    {
        return $this->hasMany(RegistrationValue::class, 'field_id');
    }

    public function registrationValuesExist(): bool
    {
        return once(fn (): bool => $this->registrationValues()->exists());
    }
}
