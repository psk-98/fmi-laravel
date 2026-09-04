<?php

namespace App\Http\Controllers\Api\V1\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Gallery\StoreGalleryRequest;
use App\Http\Requests\Api\V1\Gallery\UpdateGalleryRequest;
use App\Http\Resources\Api\V1\GalleryResource;
use App\Models\Gallery;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class GalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $galleries = Gallery::query()
            ->select(['id', 'user_id', 'name', 'description', 'uid', 'visibility', 'created_at', 'updated_at'])
            ->with('user:id,name')
            ->withCount('images')
            ->where('visibility', Gallery::VISIBILITY_PUBLIC)
            ->latest()
            ->paginate();

        return GalleryResource::collection($galleries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGalleryRequest $request): GalleryResource
    {
        logger($request);
        $gallery = $request->user()->galleries()->create($request->validated());

        return new GalleryResource($gallery->load('user')->loadCount('images'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Gallery $gallery): GalleryResource
    {
        // abort_unless($gallery->visibility === Gallery::VISIBILITY_PUBLIC, 404);

        $gallery->load([
            'user:id,name',
            'images' => fn($query) => $query
                ->withCount('embeddings')
                ->where('is_public', true)
                ->where('moderation_status', 'approved')
                ->where('processing_status', 'processed')
                ->latest(),
        ])->loadCount('images');

        return new GalleryResource($gallery);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGalleryRequest $request, Gallery $gallery): GalleryResource
    {
        $gallery->update($request->validated());

        return new GalleryResource($gallery->load('user')->loadCount('images'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gallery $gallery): Response
    {
        Gate::authorize('delete', $gallery);
        $gallery->delete();

        return response()->noContent();
    }
}
