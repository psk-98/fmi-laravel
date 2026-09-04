<?php

namespace App\Http\Controllers\Api\V1\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\GalleryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MeGalleriesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $galleries = $request->user()
            ->galleries()
            ->with('user:id,name')
            ->withCount('images')
            ->latest()
            ->paginate();

        return GalleryResource::collection($galleries);
    }
}
