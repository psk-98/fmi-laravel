<?php

namespace App\Http\Controllers\Api\V1\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\GalleryResource;
use App\Models\Gallery;
use Illuminate\Support\Facades\Gate;

class MeGalleryImagesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Gallery $gallery): GalleryResource
    {
        Gate::authorize('update', $gallery);

        $gallery->load([
            'user:id,name,email,role,created_at',
            'images' => fn ($query) => $query
                ->withCount('embeddings')
                ->latest(),
        ])->loadCount('images');

        return new GalleryResource($gallery);
    }
}
