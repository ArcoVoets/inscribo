<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionsEnum: string
{
    // Manager permissions
    case ACCESS_ADMIN_PANEL = 'access_admin_panel';
    case VIEW_REGISTRATIONS = 'view_registrations';
    case MANAGE_REGISTRATIONS = 'manage_registrations';
    case MANAGE_INVITES = 'manage_invites';
    case VIEW_EVENTS = 'view_events';
    case MANAGE_EVENTS = 'manage_events';
    case PREVIEW_FORMS = 'preview_forms';
    case MANAGE_FORMS = 'manage_forms';

    // Admin permissions
    case MANAGE_USERS = 'manage_users';
    case DELETE_FORMS = 'delete_forms';
    case MANAGE_MAILER_SETTINGS = 'manage_mailer_settings';
}
