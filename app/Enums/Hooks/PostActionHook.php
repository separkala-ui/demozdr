<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum PostActionHook: string
{
    case BEFORE_DELETED = 'post_before_deleted';
    case DELETED = 'post_deleted';
    case TAXONOMIES_UPDATED = 'post_taxonomies_updated';
    case META_UPDATED = 'post_meta_updated';
    case CREATED = 'post_created';
    case UPDATED = 'post_updated';
    case PUBLISHED = 'post_published';
    case UNPUBLISHED = 'post_unpublished';
}
