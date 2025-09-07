<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum PostFilterHook: string
{
    case POST_CREATED_BEFORE = 'filter_post_created_before';
    case POST_CREATED_AFTER = 'filter_post_created_after';

    case POST_UPDATED_BEFORE = 'filter_post_updated_before';
    case POST_UPDATED_AFTER = 'filter_post_updated_after';

    case POST_DELETED_BEFORE = 'filter_post_deleted_before';
    case POST_DELETED_AFTER = 'filter_post_deleted_after';

    case POST_CONTENT_FILTER = 'filter_post_content';
    case POST_TITLE_FILTER = 'filter_post_title';
    case POST_STATUS_FILTER = 'filter_post_status';
}
