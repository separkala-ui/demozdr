<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                
                Log::info('🔄 Guest middleware triggered for authenticated user', [
                    'path' => $request->path(),
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                ]);

                // If user has dashboard permission, redirect to admin
                if ($user->can('dashboard.view')) {
                    Log::info('↪️ Redirecting authenticated user to admin dashboard', [
                        'user_id' => $user->id,
                    ]);
                    return redirect()->route('admin.dashboard');
                }

                // Otherwise, redirect to home
                Log::info('↪️ Redirecting authenticated user to home', [
                    'user_id' => $user->id,
                ]);
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
