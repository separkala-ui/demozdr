<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum UserHook: string
{
    // Actions
    case CREATE_PAGE_BEFORE = 'user_create_page_before';
    case STORE_AFTER = 'user_store_after';
    case EDIT_PAGE_BEFORE = 'user_edit_page_before';
    case UPDATE_AFTER = 'user_update_after';
    case DELETE_AFTER = 'user_delete_after';
    case PROFILE_UPDATE_AFTER = 'user_profile_update_after';
    case PROFILE_ADDITIONAL_UPDATE_AFTER = 'user_profile_additional_update_after';

    // Filters
    case STORE_BEFORE_SAVE = 'user_store_before_save';
    case STORE_AFTER_SAVE = 'user_store_after_save';
    case EDIT_PAGE_BEFORE_WITH_USER = 'user_edit_page_before_with_user';
    case UPDATE_BEFORE_SAVE = 'user_update_before_save';
    case UPDATE_AFTER_SAVE = 'user_update_after_save';
    case DELETE_BEFORE = 'user_delete_before';
}
