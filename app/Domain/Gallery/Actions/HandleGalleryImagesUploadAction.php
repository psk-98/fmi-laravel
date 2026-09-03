<?php

namespace App\Domain\Gallery\Actions;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class HandleGalleryImagesUploadAction
{
    public function execute(User $user, Gallery $gallery, array $uploadedImages)
    {
        $createAction = app(CreateGalleryImageAction::class);

        $images = new Collection();

        foreach ($uploadedImages as $uploadedImage) {
            abort_unless($uploadedImage instanceof UploadedFile, 422);

            $images->push(
                $createAction->execute(
                    $user,
                    $gallery,
                    $uploadedImage,
                ),
            );
        }

        return $images;
    }
}
