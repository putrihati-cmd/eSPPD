<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * LOGIC MAP: Approval Level Middleware
 * Check if user has required approval level to access route
 * Uses Employee.approval_level (1-6 integer) from LOGIC MAP
 *
 * Usage: middleware('approval-level:4,5,6') for Dekan+ only
 * OR: middleware('approval-level:2') for Kaprodi+ (level 2 minimum)
 */
class CheckRoleLevel
{
    public function handle(Request $request, Closure $next, ...$allowedLevels): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu');
        }

        // LOGIC MAP: Get approval_level from Employee (1-6), not User.role
        $userLevel = $user->employee?->approval_level ?? 1;
        $allowedLevels = array_map('intval', $allowedLevels);

        if (!in_array($userLevel, $allowedLevels)) {
            // Check if it's an AJAX/API request
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'Anda tidak punya akses ke fitur ini',
                    'required_level' => min($allowedLevels),
                    'your_level' => $userLevel
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak punya akses ke fitur ini');
        }

        return $next($request);
    }
}
