<?php

namespace App\Policies;

use App\Models\Extractor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExtractorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user): bool
    {
        // Allow super_admin to view any Extractor
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Allow 'tripodi' role to view any Extractor
        return $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Extractor  $extractor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Extractor $extractor): bool
    {
        // Allow super_admin to view any Extractor
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Allow 'tripodi' role to view any Extractor
        return $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user): bool
    {
        // Only allow super_admin to create Extractor
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Extractor  $extractor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Extractor $extractor): bool
    {
        // Allow super_admin to update any Extractor
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Deny 'tripodi' role from updating Extractor
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Extractor  $extractor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Extractor $extractor): bool
    {
        // Only allow super_admin to delete Extractor
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Extractor  $extractor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Extractor $extractor): bool
    {
        // Only allow super_admin to restore Extractor
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Extractor  $extractor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Extractor $extractor): bool
    {
        // Only allow super_admin to force delete Extractor
        return $user->hasRole('super_admin');
    }
} 