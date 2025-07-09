<?php

namespace App\Policies;

use App\Models\User;

use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('view_any_user') || $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('view_user') || $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('create_user') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('update_user') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('delete_user') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can bulk delete.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function deleteAny(User $user): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('delete_any_user') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('restore_user') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('force_delete_user') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can bulk restore.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restoreAny(User $user): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('restore_any_user') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can bulk permanently delete.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDeleteAny(User $user): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('force_delete_any_user') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can replicate.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function replicate(User $user): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('replicate_user') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can reorder.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function reorder(User $user): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('reorder_user') && ! $user->hasRole('tripodi');
    }
}
