<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum UserActionHook: string
{
    case USER_CREATED_BEFORE = 'action_user_created_before';
    case USER_CREATED_AFTER = 'action_user_created_after';

    case USER_UPDATED_BEFORE = 'action_user_updated_before';
    case USER_UPDATED_AFTER = 'action_user_updated_after';

    case USER_PROFILE_UPDATED_BEFORE = 'action_user_profile_updated_before';
    case USER_PROFILE_UPDATED_AFTER = 'action_user_profile_updated_after';

    case USER_DELETED_BEFORE = 'action_user_deleted_before';
    case USER_DELETED_AFTER = 'action_user_deleted_after';

    case USER_BULK_DELETED_BEFORE = 'action_user_bulk_deleted_before';
    case USER_BULK_DELETED_AFTER = 'action_user_bulk_deleted_after';
}
