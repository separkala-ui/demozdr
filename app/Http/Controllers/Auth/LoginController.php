<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // AuthenticatesUsers trait handles guest middleware internally
        // No need to add it here - it would cause infinite redirects
    }

    /**
     * Get the login username to be used by the controller.
     */
    public function username(): string
    {
        return 'email';
    }

    /**
     * Attempt to log the user into the application.
     */
    protected function attemptLogin(Request $request)
    {
        $login = $request->input($this->username());
        
        // Try email first
        if (Auth::attempt(['email' => $login, 'password' => $request->password], $request->filled('remember'))) {
            return true;
        }

        // Try username second
        if (Auth::attempt(['username' => $login, 'password' => $request->password], $request->filled('remember'))) {
            return true;
        }

        return false;
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        Log::info('✅ User authenticated successfully', [
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'roles' => $user->getRoleNames()->toArray(),
        ]);

        // Refresh user to get fresh permissions
        $user = $user->fresh();

        // Check if user has dashboard.view permission
        if ($user->hasPermissionTo('dashboard.view')) {
            Log::info('→ Redirecting to admin dashboard', [
                'user_id' => $user->id,
                'reason' => 'has dashboard.view permission',
            ]);
            return redirect()->route('admin.dashboard');
        }

        // Otherwise, redirect to home page
        Log::info('→ Redirecting to home page', [
            'user_id' => $user->id,
            'reason' => 'no dashboard.view permission',
        ]);
        return redirect(RouteServiceProvider::HOME);
    }
}
