<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum RoleActionHook: string
{
    case ROLE_CREATED_BEFORE = 'action_role_created_before';
    case ROLE_CREATED_AFTER = 'action_role_created_after';

    case ROLE_UPDATED_BEFORE = 'action_role_updated_before';
    case ROLE_UPDATED_AFTER = 'action_role_updated_after';

    case ROLE_DELETED_BEFORE = 'action_role_deleted_before';
    case ROLE_DELETED_AFTER = 'action_role_deleted_after';

    case ROLE_BULK_DELETED_BEFORE = 'action_role_bulk_deleted_before';
    case ROLE_BULK_DELETED_AFTER = 'action_role_bulk_deleted_after';
}
