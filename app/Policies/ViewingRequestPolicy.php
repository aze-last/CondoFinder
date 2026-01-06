<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ViewingRequest;
use Illuminate\Auth\Access\Response;

class ViewingRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->hasRole('Owner');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ViewingRequest $viewingRequest): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->id === $viewingRequest->owner_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Anyone can create
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ViewingRequest $viewingRequest): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Owner can accept/decline, so update is allowed if owner
        return $user->id === $viewingRequest->owner_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ViewingRequest $viewingRequest): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->id === $viewingRequest->owner_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ViewingRequest $viewingRequest): bool
    {
        return $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ViewingRequest $viewingRequest): bool
    {
        return $user->hasRole('Super Admin');
    }
}
