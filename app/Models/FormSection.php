<?php

namespace App\Models;

use App\Observers\FormSectionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(FormSectionObserver::class)]
class FormSection extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected $attributes = [
        'sort_order' => 0,
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class, 'section_id')->orderBy('sort_order');
    }

    public function fieldsWithRegistrationsExist(): bool
    {
        return once(fn (): bool => $this->fields()->whereHas('registrationValues')->exists());
    }

    public function registrationValuesExistForField(FormField|int|null $fieldId): bool
    {
        if ($fieldId === null) {
            return false;
        }

        $fieldId = $fieldId instanceof FormField ? $fieldId->id : $fieldId;

        $counts = once(fn (): array => FormField::query()
            ->where('section_id', $this->id)
            ->withCount('registrationValues')
            ->get()
            ->pluck('registration_values_count', 'id')
            ->toArray()
        );

        return $counts[$fieldId] ?? false;
    }
}
