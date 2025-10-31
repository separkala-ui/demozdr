<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DetectDocker
{
    public function handle(Request $request, Closure $next)
    {
        if (! app()->environment('testing') && ! app()->runningUnitTests()) {
            if (! app()->has('isDocker')) {
                app()->instance('isDocker', $this->isDockerEnvironment());
            }
        }

        return $next($request);
    }

    protected function isDockerEnvironment(): bool
    {
        if (file_exists(base_path('docker/.in-docker'))) {
            return true;
        }

        return file_exists('/.dockerenv')
            || (is_dir('/proc/1') && trim((string) @file_get_contents('/proc/1/cgroup')) !== '')
                && str_contains((string) @file_get_contents('/proc/1/cgroup'), 'docker');
    }
}
