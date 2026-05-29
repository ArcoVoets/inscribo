<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormFieldOption extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected $attributes = [
        'sort_order' => 0,
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'field_id');
    }

    public function registrationValues(): HasMany
    {
        return $this->hasMany(RegistrationValue::class, 'option_id');
    }
}
