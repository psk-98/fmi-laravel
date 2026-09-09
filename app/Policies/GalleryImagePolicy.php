<?php

namespace App\Policies;

use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\User;

class GalleryImagePolicy
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
    public function view(User $user, GalleryImage $galleryImage): bool
    {
        return $user->isAdmin() || $galleryImage->gallery()->whereBelongsTo($user)->exists()
            || ($galleryImage->is_public && $galleryImage->gallery()->where('visibility', Gallery::VISIBILITY_PUBLIC)->exists());
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
    public function update(User $user, GalleryImage $galleryImage): bool
    {
        return $user->isAdmin() || $galleryImage->gallery()->whereBelongsTo($user)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GalleryImage $galleryImage): bool
    {
        return $user->isAdmin() || $galleryImage->gallery()->whereBelongsTo($user)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GalleryImage $galleryImage): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GalleryImage $galleryImage): bool
    {
        return false;
    }
}
