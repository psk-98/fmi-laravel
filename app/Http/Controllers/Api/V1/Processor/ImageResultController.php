<?php

namespace App\Http\Controllers\Api\V1\Processor;

use App\Domain\Gallery\Actions\StoreImageEmbedding;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Gallery\ProcessorImageResultRequest;
use App\Http\Resources\Api\V1\GalleryImageResource;
use App\Models\GalleryImage;

class ImageResultController extends Controller
{
    public function __invoke(ProcessorImageResultRequest $request, GalleryImage $galleryImage,): GalleryImageResource
    {
        $image = app(StoreImageEmbedding::class)->execute($galleryImage, $request->validated());

        return new GalleryImageResource($image);
    }
}
