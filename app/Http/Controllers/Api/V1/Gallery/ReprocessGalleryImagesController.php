<?php

namespace App\Http\Controllers\Api\V1\Gallery;

use App\Domain\Gallery\Actions\QueueGalleryImageForProcessingAction;
use App\Domain\Gallery\Enums\ProcessingStatus;
use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ReprocessGalleryImagesController extends Controller
{
    public function __invoke(
        Gallery $gallery,
        QueueGalleryImageForProcessingAction $queueImage,
    ): JsonResponse {
        Gate::authorize('update', $gallery);

        $images = $gallery->images()
            ->whereIn('processing_status', [
                ProcessingStatus::Pending->value,
                ProcessingStatus::Failed->value,
            ])
            ->get();

        $queued = $images->sum(
            fn ($image): int => $queueImage->execute($image) ? 1 : 0,
        );

        return response()->json([
            'message' => $queued === 1
                ? 'One unprocessed image was queued.'
                : "{$queued} unprocessed images were queued.",
            'queued' => $queued,
        ], 202);
    }
}
