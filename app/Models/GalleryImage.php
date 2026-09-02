<?php

namespace App\Models;

use App\Domain\Gallery\Enums\ModerationStatus;
use App\Domain\Gallery\Enums\ProcessingStatus;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


#[Guarded([])]
class GalleryImage extends Model
{
    protected $attributes = [
        'disk' => 'public',
        'processing_status' => ProcessingStatus::Pending->value,
        'moderation_status' => ModerationStatus::Pending->value,
        'is_public' => false,
    ];

    protected function casts(): array
    {
        return [
            'processing_status' => ProcessingStatus::class,
            'moderation_status' => ModerationStatus::class,
            'processed_at' => 'datetime',
            'moderated_at' => 'datetime',
            'is_public' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (GalleryImage $image): void {
            $image->uid ??= (string) Str::uuid();
        });

        static::deleted(function (GalleryImage $image): void {
            Storage::disk($image->disk)->delete($image->path);
        });
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function embeddings(): HasMany
    {
        return $this->hasMany(GalleryImageEmbedding::class)->orderBy('face_index');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function getRouteKeyName(): string
    {
        return 'uid';
    }
}
