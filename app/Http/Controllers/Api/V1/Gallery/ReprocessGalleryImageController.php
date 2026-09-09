<?php

namespace App\Http\Controllers\Api\V1\Gallery;

use App\Domain\Gallery\Actions\QueueGalleryImageForProcessingAction;
use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ReprocessGalleryImageController extends Controller
{
    public function __invoke(
        GalleryImage $galleryImage,
        QueueGalleryImageForProcessingAction $queueImage,
    ): JsonResponse {
        Gate::authorize('update', $galleryImage);

        if (! $queueImage->execute($galleryImage)) {
            return response()->json([
                'message' => 'This image is already processing.',
                'queued' => 0,
            ], 409);
        }

        return response()->json([
            'message' => 'Image queued for reprocessing.',
            'queued' => 1,
        ], 202);
    }
}
