<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum PostActionHook: string
{
    case POST_CREATED_BEFORE = 'action_post_created_before';
    case POST_CREATED_AFTER = 'action_post_created_after';

    case POST_UPDATED_BEFORE = 'action_post_updated_before';
    case POST_UPDATED_AFTER = 'action_post_updated_after';

    case POST_DELETED_BEFORE = 'action_post_deleted_before';
    case POST_DELETED_AFTER = 'action_post_deleted_after';

    case POST_BULK_DELETED_BEFORE = 'action_post_bulk_deleted_before';
    case POST_BULK_DELETED_AFTER = 'action_post_bulk_deleted_after';

    case POST_PUBLISHED_BEFORE = 'action_post_published_before';
    case POST_PUBLISHED_AFTER = 'action_post_published_after';

    case POST_TAXONOMIES_UPDATED = 'action_post_taxonomies_updated';
    case POST_META_UPDATED = 'action_post_meta_updated';
}
