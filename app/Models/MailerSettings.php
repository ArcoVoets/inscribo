<?php

namespace App\Models;

use Database\Factories\MailerSettingsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailerSettings extends Model
{
    /** @use HasFactory<MailerSettingsFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'password' => 'encrypted',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
