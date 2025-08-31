<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum PostFilterHook: string
{
    case BEFORE_SAVE = 'before_post_save';
    case AFTER_SAVE = 'after_post_save';
    case CONTENT_FILTER = 'post_content_filter';
    case TITLE_FILTER = 'post_title_filter';
    case STATUS_FILTER = 'post_status_filter';
}
