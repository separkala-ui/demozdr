<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum RoleFilterHook: string
{
    case ROLE_CREATED_BEFORE = 'filter_role_created_before';
    case ROLE_CREATED_AFTER = 'filter_role_created_after';

    case ROLE_UPDATED_BEFORE = 'filter_role_updated_before';
    case ROLE_UPDATED_AFTER = 'filter_role_updated_after';

    case ROLE_DELETED_BEFORE = 'filter_role_deleted_before';
    case ROLE_DELETED_AFTER = 'filter_role_deleted_after';

    case ROLE_BULK_DELETED_BEFORE = 'filter_role_bulk_deleted_before';
    case ROLE_BULK_DELETED_AFTER = 'filter_role_bulk_deleted_after';
}
