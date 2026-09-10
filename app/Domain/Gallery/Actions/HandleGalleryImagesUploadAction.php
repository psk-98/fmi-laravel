<?php

namespace App\Domain\Gallery\Actions;

use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class HandleGalleryImagesUploadAction
{
    public function __construct(private readonly CreateGalleryImageAction $createGalleryImage) {}

    /**
     * @param  array<int, UploadedFile>  $uploadedImages
     * @return Collection<int, GalleryImage>
     */
    public function execute(User $user, Gallery $gallery, array $uploadedImages): Collection
    {
        $images = new Collection;
        $requestedBytes = 0;

        foreach ($uploadedImages as $uploadedImage) {
            abort_unless($uploadedImage instanceof UploadedFile, 422);
            $requestedBytes += (int) $uploadedImage->getSize();
        }

        try {
            return DB::transaction(function () use ($user, $gallery, $uploadedImages, $images, $requestedBytes): Collection {
                $galleryOwner = User::query()
                    ->lockForUpdate()
                    ->findOrFail($gallery->user_id);
                $storageUsedBytes = $galleryOwner->storageUsedBytes();
                $storageQuotaBytes = (int) $galleryOwner->storage_quota_bytes;

                if ($storageUsedBytes + $requestedBytes > $storageQuotaBytes) {
                    throw ValidationException::withMessages([
                        'images' => [
                            'There is not enough storage space for these images. Delete images or a gallery and try again.',
                        ],
                    ]);
                }

                foreach ($uploadedImages as $uploadedImage) {
                    $images->push(
                        $this->createGalleryImage->execute(
                            $user,
                            $gallery,
                            $uploadedImage,
                        ),
                    );
                }

                return $images;
            });
        } catch (Throwable $exception) {
            $images->each(function (GalleryImage $image): void {
                Storage::disk($image->disk)->delete($image->path);
            });

            throw $exception;
        }
    }
}
