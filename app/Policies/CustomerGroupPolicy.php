<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CustomerGroup;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerGroupPolicy
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
        return $user->can('view_any_customer::group') || $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CustomerGroup  $customerGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, CustomerGroup $customerGroup): bool
    {
        return $user->can('view_customer::group') || $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user): bool
    {
        return $user->can('create_customer::group') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CustomerGroup  $customerGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, CustomerGroup $customerGroup): bool
    {
        return $user->can('update_customer::group') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CustomerGroup  $customerGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, CustomerGroup $customerGroup): bool
    {
        return $user->can('delete_customer::group') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can bulk delete.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_customer::group') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CustomerGroup  $customerGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, CustomerGroup $customerGroup): bool
    {
        return $user->can('restore_customer::group') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CustomerGroup  $customerGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, CustomerGroup $customerGroup): bool
    {
        return $user->can('force_delete_customer::group') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can bulk restore.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_customer::group') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can bulk permanently delete.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_customer::group') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can replicate.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CustomerGroup  $customerGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function replicate(User $user, CustomerGroup $customerGroup): bool
    {
        return $user->can('replicate_customer::group') && ! $user->hasRole('tripodi');
    }

    /**
     * Determine whether the user can reorder.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_customer::group') && ! $user->hasRole('tripodi');
    }

} 