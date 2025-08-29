<?php

declare(strict_types=1);

namespace App\Enums\Hooks;

enum DatatableHook: string
{
    // Actions

    // Filters
    case BEFORE_SEARCHBOX = 'datatable_before_searchbox';
    case AFTER_SEARCHBOX = 'datatable_after_searchbox';
}
