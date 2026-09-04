<?php

namespace App\Domain\Gallery\Actions;

use App\Domain\Gallery\Enums\ProcessingStatus;
use App\Models\GalleryImage;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StoreImageEmbedding
{

    public function execute(GalleryImage $image, array $result): GalleryImage
    {
        $faces = $this->validatedFaces($result);

        DB::transaction(function () use ($image, $result, $faces): void {
            $updates = [
                'processing_status' => ProcessingStatus::Processed,
                'processing_error' => null,
                'processed_at' => now(),
            ];

            foreach (['description',  'width', 'height'] as $attribute) {
                if (array_key_exists($attribute, $result)) {
                    $updates[$attribute] = $result[$attribute];
                }
            }

            $image->update($updates);
            $image->embeddings()->delete();

            foreach ($faces as $face) {
                $image->embeddings()->create($face);
            }
        });

        return $image->fresh(['embeddings', 'gallery.user'])->loadCount('embeddings');
    }

    /**
     * @param  array<string, mixed>  $result
     * @return Collection<int, array<string, mixed>>
     */
    private function validatedFaces(array $result): Collection
    {
        $rawFaces = Arr::get($result, 'faces');

        if (! is_array($rawFaces)) {
            $legacyEmbedding = Arr::get($result, 'embedding');

            if ($legacyEmbedding === null) {
                throw new InvalidArgumentException('The processor must return a faces array.');
            }

            $rawFaces = [['face_index' => 0, 'embedding' => $legacyEmbedding]];
        }

        if (count($rawFaces) > (int) config('services.image_processor.max_faces')) {
            throw new InvalidArgumentException('The processor returned too many faces.');
        }

        $seenIndexes = [];

        return collect($rawFaces)->values()->map(function (mixed $face, int $fallbackIndex) use ($result, &$seenIndexes): array {
            if (! is_array($face)) {
                throw new InvalidArgumentException('Every processed face must be an object.');
            }

            $faceIndex = $face['face_index'] ?? $fallbackIndex;

            if (! is_int($faceIndex) || $faceIndex < 0 || in_array($faceIndex, $seenIndexes, true)) {
                throw new InvalidArgumentException('Every face index must be a unique non-negative integer.');
            }

            $seenIndexes[] = $faceIndex;
            $vector = $this->validatedVector($face['embedding'] ?? null);
            $detectionScore = $face['detection_score'] ?? null;

            if ($detectionScore !== null && (! is_numeric($detectionScore) || ! is_finite((float) $detectionScore))) {
                throw new InvalidArgumentException('Face detection scores must be finite numbers.');
            }

            return [
                'face_index' => $faceIndex,
                'bounding_box' => $this->validatedBoundingBox($face['bounding_box'] ?? null),
                'detection_score' => $detectionScore === null ? null : (float) $detectionScore,
                'embedding' => $vector,
                'model' => Arr::get($face, 'model', Arr::get($result, 'model', config('services.image_processor.model'))),
                'dimensions' => count($vector),
                'metadata' => [
                    'image' => Arr::get($result, 'metadata', []),
                    'face' => Arr::get($face, 'metadata', []),
                ],
            ];
        });
    }

    /** @return array{x: int, y: int, width: int, height: int}|null */
    private function validatedBoundingBox(mixed $boundingBox): ?array
    {
        if ($boundingBox === null) {
            return null;
        }

        if (! is_array($boundingBox)) {
            throw new InvalidArgumentException('Face bounding boxes must be objects.');
        }

        foreach (['x', 'y', 'width', 'height'] as $key) {
            if (! isset($boundingBox[$key]) || ! is_int($boundingBox[$key])) {
                throw new InvalidArgumentException('Face bounding boxes must contain integer coordinates.');
            }
        }

        if ($boundingBox['x'] < 0 || $boundingBox['y'] < 0 || $boundingBox['width'] < 1 || $boundingBox['height'] < 1) {
            throw new InvalidArgumentException('Face bounding boxes must have valid dimensions.');
        }

        return [
            'x' => $boundingBox['x'],
            'y' => $boundingBox['y'],
            'width' => $boundingBox['width'],
            'height' => $boundingBox['height'],
        ];
    }

    /** @return array<float> */
    private function validatedVector(mixed $embedding): array
    {
        $expectedDimensions = (int) config('services.image_processor.embedding_dimensions');

        if (! is_array($embedding) || count($embedding) !== $expectedDimensions) {
            throw new InvalidArgumentException("The processor must return a {$expectedDimensions}-dimension embedding.");
        }

        return array_map(static function (mixed $component): float {
            if (! is_numeric($component) || ! is_finite((float) $component)) {
                throw new InvalidArgumentException('Embedding components must be finite numbers.');
            }

            return (float) $component;
        }, $embedding);
    }
}
