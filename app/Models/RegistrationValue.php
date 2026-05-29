<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationValue extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'option_price_cents' => 'integer',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'field_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(FormFieldOption::class, 'option_id');
    }

    public function showValue(): string
    {
        if ($this->option) {
            return $this->option->label;
        }

        return $this->value;
    }
}
