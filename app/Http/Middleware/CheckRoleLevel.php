<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RBAC.md Implementation: Role Level Middleware
 * Check if user has minimum role level to access route
 * 
 * Usage: middleware('role.level:3') for Wadek+ only
 */
class CheckRoleLevel
{
    public function handle(Request $request, Closure $next, int $minLevel): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu');
        }

        $userLevel = $user->role_level ?? 1;

        if ($userLevel < $minLevel) {
            // Check if it's an AJAX/API request
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'Anda tidak punya akses ke fitur ini',
                    'required_level' => $minLevel,
                    'your_level' => $userLevel
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak punya akses ke fitur ini');
        }

        return $next($request);
    }
}
