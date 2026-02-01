<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->is_password_reset) {
            if (!$request->routeIs('password.force-change') && !$request->routeIs('logout')) {
                return redirect()->route('password.force-change');
            }
        }
        return $next($request);
    }
}
