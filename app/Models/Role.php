<?php

namespace App\Models;

use App\Concerns\QueryBuilderTrait;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use QueryBuilderTrait;

    public const SUPERADMIN = 'Superadmin';
    public const EDITOR = 'Editor';
    public const ADMIN = 'Admin';
    public const SUBSCRIBER = 'Subscriber';

    /**
     * Get searchable columns for the model.
     */
    protected function getSearchableColumns(): array
    {
        return ['name'];
    }

    /**
     * Get columns that should be excluded from sorting.
     */
    protected function getExcludedSortColumns(): array
    {
        return ['user_count'];
    }
}
