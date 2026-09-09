<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\AsVector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Guarded([])]
#[Hidden(['embedding'])]
class GalleryImageEmbedding extends Model
{
    protected $attributes = [
        'face_index' => 0,
        'model' => 'clip-vit-base-patch32',
        'dimensions' => 512,
    ];

    public function galleryImage(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class);
    }

    protected function casts(): array
    {
        return [
            'embedding' => AsVector::class,
            'bounding_box' => 'array',
            'detection_score' => 'float',
            'metadata' => 'array',
            'dimensions' => 'integer',
        ];
    }
}
