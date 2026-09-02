<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;


#[Fillable(['user_id', 'name', 'description', 'uid', 'visibility'])]
class Gallery extends Model
{
    public const VISIBILITY_PRIVATE = 'private';

    public const VISIBILITY_PUBLIC = 'public';

    protected $attributes = [
        'visibility' => self::VISIBILITY_PRIVATE,
    ];

    protected static function booted(): void
    {
        static::creating(function (Gallery $gallery): void {
            $gallery->uid ??= (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uid';
    }
}
