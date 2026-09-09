<?php

namespace App\Jobs;

use App\Domain\Gallery\Actions\StoreImageEmbedding;
use App\Domain\Gallery\Enums\ProcessingStatus;
use App\Domain\ImageProcessing\Actions\ProcessImageAction;
use App\Models\GalleryImage;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

class ProcessGalleryImage implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $galleryImageId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $image = GalleryImage::find($this->galleryImageId);

        if ($image === null) {
            return;
        }

        $image->update([
            'processing_status' => ProcessingStatus::Processing,
            'processing_error' => null,
        ]);

        $result = app(ProcessImageAction::class)->execute($image);
        app(StoreImageEmbedding::class)->execute($image, $result);
    }

    public function failed(Throwable $exception): void
    {
        GalleryImage::query()->whereKey($this->galleryImageId)->update([
            'processing_status' => ProcessingStatus::Failed,
            'processing_error' => Str::limit($exception->getMessage(), 2000),
        ]);
    }
}
