<?php

declare(strict_types=1);

if (! function_exists('toast')) {
    /**
     * Display a toast notification
     *
     * @param  string  $message  The message to display
     * @param  string  $type  The type of notification (success, error, warning, info)
     * @param  int  $duration  Duration in milliseconds
     */
    function toast(string $message, string $type = 'success', int $duration = 5000): void
    {
        session()->flash('toast', [
            'message' => $message,
            'type' => $type,
            'duration' => $duration,
        ]);
    }
}

if (! function_exists('toast_success')) {
    /**
     * Display a success toast notification
     */
    function toast_success(string $message, int $duration = 5000): void
    {
        toast($message, 'success', $duration);
    }
}

if (! function_exists('toast_error')) {
    /**
     * Display an error toast notification
     */
    function toast_error(string $message, int $duration = 5000): void
    {
        toast($message, 'error', $duration);
    }
}

if (! function_exists('toast_warning')) {
    /**
     * Display a warning toast notification
     */
    function toast_warning(string $message, int $duration = 5000): void
    {
        toast($message, 'warning', $duration);
    }
}

if (! function_exists('toast_info')) {
    /**
     * Display an info toast notification
     */
    function toast_info(string $message, int $duration = 5000): void
    {
        toast($message, 'info', $duration);
    }
}

