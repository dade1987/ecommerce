<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
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
        return $user->can('view_any_role') || $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Role $role): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('view_role') || $user->hasRole('tripodi');
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
        return $user->can('create_role') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Role $role): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('update_role') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Role $role): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('delete_role') && ! $user->hasRole('tripodi');
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
        return $user->can('delete_any_role') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Role $role): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('restore_role') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Role $role): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('force_delete_role') && ! $user->hasRole('tripodi');
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
        return $user->can('restore_any_role') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can permanently bulk delete.
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
        return $user->can('force_delete_any_role') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can replicate.
     *
     * @param  \App\Models\User  $user
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function replicate(User $user, Role $role): bool
    {
        // Allow super_admin full access
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return $user->can('replicate_role') && ! $user->hasRole('tripodi');
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
        return $user->can('reorder_role') && ! $user->hasRole('tripodi');
    }

}
