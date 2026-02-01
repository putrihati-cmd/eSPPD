<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     * RBAC.md: Gate definitions for access control
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // RBAC.md: Admin access (superadmin, admin)
        Gate::define('access-admin', function ($user) {
            return $user->isAdmin();
        });

        // RBAC.md: Can approve SPD (level >= 2, Kaprodi ke atas)
        Gate::define('approve-sppd', function ($user) {
            return $user->role_level >= 2 || $user->isAdmin();
        });

        // RBAC.md: Executive approve (level >= 4, Dekan ke atas)
        Gate::define('approve-executive', function ($user) {
            return $user->role_level >= 4;
        });

        // RBAC.md: View all SPD in faculty (level >= 3, Wadek ke atas)
        Gate::define('view-all-sppd', function ($user) {
            return $user->role_level >= 3 || $user->isAdmin();
        });

        // RBAC.md: Manage employees (admin only)
        Gate::define('manage-employees', function ($user) {
            return $user->isAdmin();
        });

        // btn.md: Override/force cancel (Dekan+ or admin)
        Gate::define('override-sppd', function ($user) {
            return $user->canOverride();
        });

        // btn.md: Delegate approval (Wadek+ level >= 3)
        Gate::define('delegate-approval', function ($user) {
            return $user->canDelegate();
        });

        // btn.md: Download documents (approved SPD owners, approvers, admin)
        Gate::define('download-documents', function ($user, $spd) {
            return $user->employee_id === $spd->employee_id ||
                   $user->isAdmin() ||
                   $user->role_level >= 2;
        });

        // btn.md: Edit SPD (only draft, only owner)
        Gate::define('edit-sppd', function ($user, $spd) {
            return $spd->status === 'draft' && 
                   $user->employee_id === $spd->employee_id;
        });

        // btn.md: Cancel SPD (draft/submitted, owner or override)
        Gate::define('cancel-sppd', function ($user, $spd) {
            $isDraftOrSubmitted = in_array($spd->status, ['draft', 'submitted']);
            $isOwner = $user->employee_id === $spd->employee_id;
            
            return ($isDraftOrSubmitted && $isOwner) || $user->canOverride();
        });
    }
}
