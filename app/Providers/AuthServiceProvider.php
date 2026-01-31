<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
use App\Services\RbacService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Spd::class => \App\Policies\SpdPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Admin bypass - can do anything
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        // SPD Permissions
        Gate::define('create-spd', function (User $user) {
            return RbacService::userHasPermission($user, 'spd.create');
        });

        Gate::define('approve-spd', function (User $user) {
            return $user->isApprover();
        });

        Gate::define('reject-spd', function (User $user) {
            return $user->isApprover();
        });

        Gate::define('view-all-spd', function (User $user) {
            return $user->role_level >= 3; // Wadek+
        });

        // Approval Permissions
        Gate::define('delegate-approval', function (User $user) {
            return $user->canDelegate();
        });

        Gate::define('override-approval', function (User $user) {
            return $user->canOverride();
        });

        // Finance Permissions
        Gate::define('view-budget', function (User $user) {
            return $user->isFinance();
        });

        Gate::define('manage-budget', function (User $user) {
            return $user->isExecutive();
        });

        // Dynamic permission gate
        Gate::define('has-permission', function (User $user, string $permission) {
            return RbacService::userHasPermission($user, $permission);
        });

        // Budget approval gate
        Gate::define('approve-budget', function (User $user, int $amount) {
            return RbacService::canApproveAmount($user, $amount);
        });
    }
}
