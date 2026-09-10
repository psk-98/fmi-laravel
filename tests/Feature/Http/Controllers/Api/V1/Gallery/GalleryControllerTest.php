<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(LazilyRefreshDatabase::class);

it('deletes a gallery with all images and releases the used storage', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $gallery = $user->galleries()->create([
        'name' => 'Portraits',
        'visibility' => 'private',
    ]);
    $firstImage = $gallery->images()->create([
        'path' => 'gallery-images/first.jpg',
        'original_name' => 'first.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 10 * 1024,
    ]);
    $secondImage = $gallery->images()->create([
        'path' => 'gallery-images/second.jpg',
        'original_name' => 'second.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 20 * 1024,
    ]);
    Storage::disk('public')->put($firstImage->path, 'first');
    Storage::disk('public')->put($secondImage->path, 'second');
    Sanctum::actingAs($user);

    $response = $this->deleteJson(route('api.v1.galleries.destroy', $gallery));

    $response->assertNoContent();
    $this->assertModelMissing($gallery);
    $this->assertModelMissing($firstImage);
    $this->assertModelMissing($secondImage);
    Storage::disk('public')->assertMissing($firstImage->path);
    Storage::disk('public')->assertMissing($secondImage->path);
    expect($user->fresh()->storageUsedBytes())->toBe(0);
});

it('returns 403 when another user deletes a gallery', function () {
    Storage::fake('public');
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $gallery = $owner->galleries()->create([
        'name' => 'Portraits',
        'visibility' => 'private',
    ]);
    $image = $gallery->images()->create([
        'path' => 'gallery-images/portrait.jpg',
        'original_name' => 'portrait.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 10 * 1024,
    ]);
    Storage::disk('public')->put($image->path, 'image');
    Sanctum::actingAs($otherUser);

    $response = $this->deleteJson(route('api.v1.galleries.destroy', $gallery));

    $response->assertForbidden();
    $this->assertModelExists($gallery);
    $this->assertModelExists($image);
    Storage::disk('public')->assertExists($image->path);
});
