<?php

namespace App\Policies;

use App\Models\Gallery;
use App\Models\User;

class GalleryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // return $user->canModerate();
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Gallery $gallery): bool
    {
        return $gallery->user_id === $user->id
            || $gallery->visibility === Gallery::VISIBILITY_PUBLIC;
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
    public function update(User $user, Gallery $gallery): bool
    {
        return $user->isAdmin() || $gallery->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Gallery $gallery): bool
    {
        return $user->isAdmin() || $gallery->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Gallery $gallery): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Gallery $gallery): bool
    {
        return false;
    }
}
