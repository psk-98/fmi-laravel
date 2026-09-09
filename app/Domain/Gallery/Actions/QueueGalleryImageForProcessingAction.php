<?php

namespace App\Domain\Gallery\Actions;

use App\Domain\Gallery\Enums\ProcessingStatus;
use App\Jobs\ProcessGalleryImage;
use App\Models\GalleryImage;

class QueueGalleryImageForProcessingAction
{
    public function execute(GalleryImage $image): bool
    {
        if ($image->processing_status === ProcessingStatus::Processing) {
            return false;
        }

        $image->update([
            'processing_status' => ProcessingStatus::Pending,
            'processing_error' => null,
            'processed_at' => null,
        ]);

        ProcessGalleryImage::dispatch($image->id)->afterCommit();

        return true;
    }
}
