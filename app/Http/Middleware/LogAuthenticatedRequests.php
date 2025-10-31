<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogAuthenticatedRequests
{
    public function handle(Request $request, Closure $next)
    {
        // Log auth state before
        if (Auth::check()) {
            Log::info('🔐 Auth check BEFORE: User is authenticated', [
                'user_id' => Auth::id(),
                'path' => $request->path(),
                'method' => $request->method(),
            ]);
        }

        $response = $next($request);

        // Log auth state after
        if (Auth::check()) {
            Log::info('🔐 Auth check AFTER: User is authenticated', [
                'user_id' => Auth::id(),
                'path' => $request->path(),
                'method' => $request->method(),
                'status' => $this->resolveStatusCode($response),
            ]);
        }

        return $response;
    }

    protected function resolveStatusCode($response): int|string
    {
        if (is_object($response)) {
            if (method_exists($response, 'status')) {
                return $response->status();
            }

            if (method_exists($response, 'getStatusCode')) {
                return $response->getStatusCode();
            }
        }

        return 'unknown';
    }
}
