<?php

namespace App\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Hook Facade
 *
 * Provides a clean interface for WordPress-style hooks and filters.
 *
 * @method static void doAction(string $tag, ...$args)
 * @method static void addAction(string $tag, callable $function_to_add, int $priority = 20, int $accepted_args = 1)
 * @method static bool removeAction(string $tag, callable $function_to_remove, int $priority = 20)
 * @method static mixed applyFilters(string $tag, $value, ...$args)
 * @method static void addFilter(string $tag, callable $function_to_add, int $priority = 20, int $accepted_args = 1)
 * @method static bool removeFilter(string $tag, callable $function_to_remove, int $priority = 20)
 */
class Hook extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'hook';
    }
}
