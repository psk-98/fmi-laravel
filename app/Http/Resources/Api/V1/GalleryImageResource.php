<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $storageUrl = Storage::disk($this->disk)->url($this->path);

        return [
            'uid' => $this->uid,
            'url' => Str::startsWith($storageUrl, ['http://', 'https://']) ? $storageUrl : url($storageUrl),
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'width' => $this->width,
            'height' => $this->height,
            'file_size' => $this->file_size,
            'description' => $this->description,
            'face_count' => $this->whenCounted('embeddings'),
            'processing_status' => $this->processing_status?->value,
            'is_public' => $this->is_public,
            'gallery' => new GalleryResource($this->whenLoaded('gallery')),
            'processed_at' => $this->processed_at,
            'created_at' => $this->created_at,
        ];
    }
}
