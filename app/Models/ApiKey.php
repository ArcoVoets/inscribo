<?php

namespace App\Models;

use Database\Factories\ApiKeyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiKey extends Model
{
    /** @use HasFactory<ApiKeyFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'key',
    ];

    protected function casts(): array
    {
        return [
            'key' => 'encrypted',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
