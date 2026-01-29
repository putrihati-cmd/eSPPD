<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordReset
{
    /**
     * Handle an incoming request.
     * 
     * Redirect users to force password change if they haven't reset their default password.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user hasn't changed their default password
            if (!$user->is_password_reset) {
                // Allow access to password change routes
                $allowedRoutes = [
                    'password.force-change',
                    'password.force-update',
                    'logout'
                ];
                
                if (!$request->routeIs($allowedRoutes)) {
                    return redirect()->route('password.force-change')
                        ->with('warning', 'Anda harus mengganti password default sebelum melanjutkan.');
                }
            }
        }

        return $next($request);
    }
}
