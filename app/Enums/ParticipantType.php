<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ParticipantType: string implements HasLabel
{
    case Student = 'student';
    case Worker = 'worker';

    public static function values(): array
    {
        return array_map(fn (self $type) => $type->value, self::cases());
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Student => __('registration.participant_types.student'),
            self::Worker => __('registration.participant_types.worker'),
        };
    }
}
