<?php

namespace App\Http\Controllers\Api\V1\Gallery;

use App\Domain\Gallery\Actions\HandleGalleryImagesUploadAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Gallery\StoreGalleryImagesRequest;
use App\Http\Resources\Api\V1\GalleryImageResource;
use App\Models\Gallery;
use App\Models\GalleryImage;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class GalleryImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Gallery $gallery): AnonymousResourceCollection
    {
        abort_unless($gallery->visibility === Gallery::VISIBILITY_PUBLIC, 404);

        $images = $gallery->images()
            ->with('gallery.user')
            ->withCount('embeddings')
            ->where('is_public', true)
            ->where('moderation_status', 'approved')
            ->where('processing_status', 'processed')
            ->latest()
            ->paginate();

        return GalleryImageResource::collection($images);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreGalleryImagesRequest $request,
        Gallery $gallery,
    ): AnonymousResourceCollection {
        $uploadedImages = $request->file('images');

        abort_unless(is_array($uploadedImages), 422);

        $images = app(HandleGalleryImagesUploadAction::class)->execute(
            $request->user(),
            $gallery,
            $uploadedImages,
            // $request->safe()->except('image'),
        );

        return GalleryImageResource::collection($images);
    }

    /**
     * Display the specified resource.
     */
    public function show(GalleryImage $galleryImage): GalleryImageResource
    {
        $galleryImage->load('gallery.user')->loadCount('embeddings');

        abort_unless(
            $galleryImage->is_public
                && $galleryImage->moderation_status->value === 'approved'
                && $galleryImage->processing_status->value === 'processed'
                && $galleryImage->gallery->visibility === Gallery::VISIBILITY_PUBLIC,
            404,
        );

        return new GalleryImageResource($galleryImage);
    }

    /**
     * Update the specified resource in storage.
     */
    public function destroy(GalleryImage $galleryImage): Response
    {
        Gate::authorize('delete', $galleryImage);
        $galleryImage->delete();

        return response()->noContent();
    }
}
