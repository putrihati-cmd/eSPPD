<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApprovalLevel
{
    /**
     * Handle incoming request
     *
     * Usage: ->middleware('approval-level:4,5,6')
     * Allows users with approval level 4, 5, or 6
     */
    public function handle(Request $request, Closure $next, ...$allowedLevels): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        // Get user's approval level from employee relation
        $userLevel = $user->employee?->approval_level ?? 1;

        // Convert string levels to integers
        $allowedLevels = array_map('intval', $allowedLevels);

        if (!in_array($userLevel, $allowedLevels)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini. Level minimum: ' . min($allowedLevels));
        }

        return $next($request);
    }
}
