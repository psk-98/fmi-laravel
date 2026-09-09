<?php

namespace App\Http\Controllers\Api\V1\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\GalleryImageResource;
use App\Models\GalleryImage;
use Illuminate\Support\Facades\Gate;

class MeGalleryImageController extends Controller
{
    public function __invoke(GalleryImage $galleryImage): GalleryImageResource
    {
        Gate::authorize('update', $galleryImage);

        $galleryImage->load('gallery.user')->loadCount('embeddings');

        return new GalleryImageResource($galleryImage);
    }
}
