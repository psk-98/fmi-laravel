<?php

namespace App\Http\Controllers\Api\V1\Gallery;

use App\Domain\ImageProcessing\Actions\GetImageEmbeddingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Gallery\SearchImagesRequest;
use App\Http\Resources\Api\V1\ImageSearchResultResource;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Services\ImageSimilaritySearch;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;

class GalleryImageSearchController extends Controller
{
    public function __invoke(
        SearchImagesRequest $request,
        Gallery $gallery,
        GetImageEmbeddingAction $getImageEmbedding,
        ImageSimilaritySearch $similaritySearch,
    ): AnonymousResourceCollection {
        Gate::authorize('update', $gallery);

        $validated = $request->validated();
        $embeddings = $this->embeddings($request, $validated, $getImageEmbedding);

        abort_if($embeddings === [], 422, 'No face embedding was available for this search.');

        $results = $similaritySearch
            ->queryMany(
                embeddings: $embeddings,
                user: $request->user(),
                includePrivate: false,
                galleryId: $gallery->id,
            )
            ->limit((int) ($validated['limit'] ?? 20))
            ->get();

        $results->load('gallery.user')->loadCount('embeddings');

        return ImageSearchResultResource::collection($results);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, array<int, float|int>>
     */
    private function embeddings(
        SearchImagesRequest $request,
        array $validated,
        GetImageEmbeddingAction $getImageEmbedding,
    ): array {
        $image = $request->file('image');

        if ($image instanceof UploadedFile) {
            $response = $getImageEmbedding->execute($image);
            $faces = is_array($response['faces'] ?? null) ? $response['faces'] : [];

            return collect($faces)
                ->pluck('embedding')
                ->filter(fn (mixed $embedding): bool => is_array($embedding) && $embedding !== [])
                ->values()
                ->all();
        }

        $source = GalleryImage::query()
            ->where('uid', $validated['gallery_image_uid'])
            ->firstOrFail();
        Gate::authorize('view', $source);

        $source->loadMissing('embeddings');

        return $source->embeddings
            ->pluck('embedding')
            ->filter(fn (mixed $embedding): bool => is_array($embedding) && $embedding !== [])
            ->values()
            ->all();
    }
}
