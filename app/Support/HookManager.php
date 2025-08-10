<?php

declare(strict_types=1);

namespace App\Support;

use TorMorten\Eventy\Facades\Events as Eventy;

/**
 * Hook Service
 *
 * Provides WordPress-style hooks and filters functionality.
 */
class HookManager
{
    /**
     * Calls all functions attached to an action hook.
     *
     * @param string $tag The name of the action to be executed.
     * @param mixed ...$args Optional. Additional arguments which are passed on to the functions hooked to the action.
     * @return void
     */
    public function doAction(string $tag, ...$args): void
    {
        Eventy::action($tag, ...$args);
    }

    /**
     * Hooks a function on to a specific action.
     *
     * @param string $tag The name of the action to which the $function_to_add is hooked.
     * @param callable $function_to_add The name of the function you wish to be called.
     * @param int $priority Optional. Used to specify the order in which the functions are executed. Default 20.
     * @param int $accepted_args Optional. The number of arguments the function accepts. Default 1.
     * @return void
     */
    public function addAction(string $tag, callable $function_to_add, int $priority = 20, int $accepted_args = 1): void
    {
        Eventy::addAction($tag, $function_to_add, $priority, $accepted_args);
    }

    /**
     * Removes a function from a specified action hook.
     *
     * @param string $tag The action hook to which the function to be removed is hooked.
     * @param callable $function_to_remove The name of the function which should be removed.
     * @param int $priority Optional. The priority of the function. Default 20.
     * @return void
     */
    public function removeAction(string $tag, callable $function_to_remove, int $priority = 20): void
    {
        Eventy::removeAction($tag, $function_to_remove, $priority);
    }

    /**
     * Call the functions added to a filter hook.
     *
     * @param string $tag The name of the filter hook.
     * @param mixed $value The value on which the filters hooked to $tag are applied on.
     * @param mixed ...$args Optional. Additional variables passed to the functions hooked to $tag.
     * @return mixed The filtered value after all hooked functions are applied to it.
     */
    public function applyFilters(string $tag, $value, ...$args)
    {
        return Eventy::filter($tag, $value, ...$args);
    }

    /**
     * Hooks a function to a specific filter hook.
     *
     * @param string $tag The name of the filter to hook the $function_to_add callback to.
     * @param callable $function_to_add The callback to be run when the filter is applied.
     * @param int $priority Optional. Used to specify the order in which the functions are executed. Default 20.
     * @param int $accepted_args Optional. The number of arguments the function accepts. Default 1.
     * @return void
     */
    public function addFilter(string $tag, callable $function_to_add, int $priority = 20, int $accepted_args = 1): void
    {
        Eventy::addFilter($tag, $function_to_add, $priority, $accepted_args);
    }

    /**
     * Removes a function from a specified filter hook.
     *
     * @param string $tag The filter hook to which the function to be removed is hooked.
     * @param callable $function_to_remove The name of the function which should be removed.
     * @param int $priority Optional. The priority of the function. Default 20.
     * @return void
     */
    public function removeFilter(string $tag, callable $function_to_remove, int $priority = 20): void
    {
        Eventy::removeFilter($tag, $function_to_remove, $priority);
    }
}
