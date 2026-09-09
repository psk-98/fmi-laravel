<?php

namespace App\Services;

use App\Domain\Gallery\Enums\ModerationStatus;
use App\Domain\Gallery\Enums\ProcessingStatus;
use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\GalleryImageEmbedding;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ImageSimilaritySearch
{
    /**
     * @param  array<int, array<int, float|int>>  $embeddings
     * @return Builder<GalleryImage>
     */
    public function queryMany(
        array $embeddings,
        User $user,
        bool $includePrivate = false,
        ?int $excludeImageId = null,
        ?int $galleryId = null,
    ): Builder {
        $vectors = $this->validatedVectors($embeddings);

        if ($vectors === []) {
            return GalleryImage::query()->whereRaw('1 = 0');
        }

        $faceDistances = null;

        foreach ($vectors as $vector) {
            $distanceQuery = GalleryImageEmbedding::query()
                ->select('gallery_image_id')
                ->selectVectorDistance('embedding', $vector, 'distance')
                ->whereNotNull('embedding');

            $faceDistances = $faceDistances === null
                ? $distanceQuery
                : $faceDistances->unionAll($distanceQuery);
        }

        $matches = DB::query()
            ->fromSub($faceDistances, 'face_distances')
            ->select('gallery_image_id')
            ->selectRaw('MIN(distance) AS distance')
            ->groupBy('gallery_image_id');

        $query = GalleryImage::query()
            ->select('gallery_images.*')
            ->addSelect('face_matches.distance')
            ->selectRaw('1 - face_matches.distance AS similarity')
            ->joinSub(
                $matches,
                'face_matches',
                'face_matches.gallery_image_id',
                '=',
                'gallery_images.id',
            )
            ->with(['gallery.user', 'embeddings'])
            ->withCount('embeddings')
            ->where('gallery_images.processing_status', ProcessingStatus::Processed->value);

        if ($galleryId !== null) {
            $query->where('gallery_images.gallery_id', $galleryId);
        } elseif (! ($includePrivate && $user->isAdmin())) {
            $query->where(function (Builder $query) use ($user): void {
                $query
                    ->whereHas(
                        'gallery',
                        fn (Builder $gallery): Builder => $gallery->where('user_id', $user->id),
                    )
                    ->orWhere(function (Builder $public): void {
                        $public
                            ->where('gallery_images.is_public', true)
                            ->where('gallery_images.moderation_status', ModerationStatus::Approved->value)
                            ->whereHas(
                                'gallery',
                                fn (Builder $gallery): Builder => $gallery->where('visibility', Gallery::VISIBILITY_PUBLIC),
                            );
                    });
            });
        }

        if ($excludeImageId !== null) {
            $query->where('gallery_images.id', '!=', $excludeImageId);
        }

        return $query->orderBy('face_matches.distance');
    }

    /**
     * @param  array<int, array<int, float|int>>  $embeddings
     * @return array<int, array<int, float>>
     */
    private function validatedVectors(array $embeddings): array
    {
        return array_map($this->validatedVector(...), array_values($embeddings));
    }

    /**
     * @param  array<int, float|int>  $embedding
     * @return array<int, float>
     */
    private function validatedVector(array $embedding): array
    {
        if ($embedding === []) {
            throw new InvalidArgumentException('A search embedding cannot be empty.');
        }

        $expectedDimensions = (int) config('services.image_processor.embedding_dimensions');

        if (count($embedding) !== $expectedDimensions) {
            throw new InvalidArgumentException("Search embeddings must contain {$expectedDimensions} dimensions.");
        }

        return array_map(static function (float|int $component): float {
            $value = (float) $component;

            if (! is_finite($value)) {
                throw new InvalidArgumentException('Search embeddings must contain finite numbers.');
            }

            return $value;
        }, $embedding);
    }
}
