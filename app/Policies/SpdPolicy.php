<?php

namespace App\Policies;

use App\Models\Spd;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SpdPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Spd $spd): bool
    {
        // User can view their own SPPD or their organization's SPPD
        return $user->id === $spd->created_by ||
               ($user->employee?->organization_id === $spd->organization_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Spd $spd): bool
    {
        // User can update their own SPPD if it's still in draft
        return $user->id === $spd->created_by && $spd->status === 'draft';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Spd $spd): bool
    {
        // User can delete their own SPPD if it's in draft
        return $user->id === $spd->created_by && $spd->status === 'draft';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Spd $spd): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Spd $spd): bool
    {
        return false;
    }
}
