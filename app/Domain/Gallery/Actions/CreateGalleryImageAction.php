<?php

namespace App\Domain\Gallery\Actions;

use App\Jobs\ProcessGalleryImage;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CreateGalleryImageAction
{
    public function execute(User $user, Gallery $gallery, UploadedFile $file): GalleryImage
    {
        if ($gallery->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        $disk = (string) config('services.image_processor.disk', 'public');
        $path = $file->store("gallery-images/{$gallery->uid}", $disk);

        if ($path === false) {
            throw new RuntimeException('The image could not be stored.');
        }

        $dimensions = getimagesize($file->getRealPath());

        try {
            $image = $gallery->images()->create([
                'path' => $path,
                'disk' => $disk,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'width' => $dimensions === false ? null : $dimensions[0],
                'height' => $dimensions === false ? null : $dimensions[1],
                'file_size' => $file->getSize() ?: null,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }

        ProcessGalleryImage::dispatch($image->id)->afterCommit();

        return $image;
    }
}
