<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum UserFilterHook: string
{
    case STORE_BEFORE = 'filter_user_store_before';
    case STORE_AFTER = 'filter_user_store_after';

    case EDIT_BEFORE = 'filter_user_edit_before';
    case EDIT_PROFILE_BEFORE = 'filter_user_edit_profile_before';
    case EDIT_AFTER = 'filter_user_edit_after';
    case EDIT_PROFILE_AFTER = 'filter_user_edit_profile_after';

    case DELETE_BEFORE = 'filter_user_delete_before';
    case DELETE_AFTER = 'filter_user_delete_after';
}
