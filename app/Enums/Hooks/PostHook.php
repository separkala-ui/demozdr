<?php

namespace App\Enums\Hooks;

enum PostHook: string
{
    // Actions
    case BEFORE_DELETED = 'post_before_deleted';
    case DELETED = 'post_deleted';
    case TAXONOMIES_UPDATED = 'post_taxonomies_updated';
    case META_UPDATED = 'post_meta_updated';
    case CREATED = 'post_created';
    case UPDATED = 'post_updated';
    case PUBLISHED = 'post_published';
    case UNPUBLISHED = 'post_unpublished';

    // Filters
    case CONTENT_FILTER = 'post_content_filter';
    case TITLE_FILTER = 'post_title_filter';
    case STATUS_FILTER = 'post_status_filter';
}
