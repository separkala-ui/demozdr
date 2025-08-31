<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum UserActionHook: string
{
    case USER_CREATED = 'hook_user_created';
    case USER_CREATED_BEFORE = 'hook_user_created_before';
    case USER_UPDATED = 'hook_user_updated';
    case USER_UPDATED_BEFORE = 'hook_user_updated_before';
    case USER_DELETED = 'hook_user_deleted';
    case USER_DELETED_BEFORE = 'hook_user_deleted_before';

    case USER_PROFILE_UPDATED = 'hook_user_profile_updated';
    case USER_PROFILE_UPDATED_BEFORE = 'hook_user_profile_updated_before';
}
