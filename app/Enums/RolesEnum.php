<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RolesEnum: string implements HasLabel
{
    case MANAGER = 'manager';

    case ADMIN = 'admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::MANAGER => __('admin.roles.manager'),
            self::ADMIN => __('admin.roles.admin'),
        };
    }
}
