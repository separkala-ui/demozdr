<?php

namespace App\Enums\Hooks;

enum ContentHook: string
{
    // Actions
    case REGISTER_POST_TYPES = 'register_post_types';
    case REGISTER_TAXONOMIES = 'register_taxonomies';
    case INIT_CONTENT_TYPES = 'init_content_types';
    case CONTENT_RENDERED = 'content_rendered';
    case MENU_REGISTERED = 'menu_registered';

    // Filters
    case CONTENT_TYPE_CONFIG = 'content_type_config';
    case TAXONOMY_CONFIG = 'taxonomy_config';
    case MENU_ITEMS = 'menu_items';
}
