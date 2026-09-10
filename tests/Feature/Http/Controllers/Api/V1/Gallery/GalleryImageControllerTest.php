<?php

use App\Jobs\ProcessGalleryImage;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(LazilyRefreshDatabase::class);

it('stores images within the owner storage quota', function () {
    Storage::fake('public');
    Queue::fake([ProcessGalleryImage::class]);
    $user = User::factory()->create(['storage_quota_bytes' => 50 * 1024]);
    $gallery = $user->galleries()->create([
        'name' => 'Portraits',
        'visibility' => 'private',
    ]);
    $firstImage = UploadedFile::fake()->image('first.jpg')->size(10);
    $secondImage = UploadedFile::fake()->image('second.jpg')->size(20);
    Sanctum::actingAs($user);

    $response = $this->postJson(
        route('api.v1.galleries.images.store', $gallery),
        ['images' => [$firstImage, $secondImage]],
    );

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data');
    expect($user->fresh()->storageUsedBytes())->toBe(30 * 1024);
    expect($gallery->images()->count())->toBe(2);
    foreach ($gallery->images as $image) {
        Storage::disk('public')->assertExists($image->path);
    }
    Queue::assertPushed(ProcessGalleryImage::class, 2);
});

it('returns 422 without storing files when upload exceeds the owner storage quota', function () {
    Storage::fake('public');
    Queue::fake([ProcessGalleryImage::class]);
    $user = User::factory()->create(['storage_quota_bytes' => 15 * 1024]);
    $gallery = $user->galleries()->create([
        'name' => 'Portraits',
        'visibility' => 'private',
    ]);
    $gallery->images()->create([
        'path' => 'gallery-images/existing.jpg',
        'original_name' => 'existing.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 10 * 1024,
    ]);
    Sanctum::actingAs($user);

    $response = $this->postJson(
        route('api.v1.galleries.images.store', $gallery),
        ['images' => [UploadedFile::fake()->image('too-large.jpg')->size(10)]],
    );

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['images'])
        ->assertJsonPath(
            'errors.images.0',
            'There is not enough storage space for these images. Delete images or a gallery and try again.',
        );
    expect($gallery->images()->count())->toBe(1);
    expect(Storage::disk('public')->allFiles())->toBe([]);
    Queue::assertNotPushed(ProcessGalleryImage::class);
});

it('releases used storage when an image is deleted', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $gallery = $user->galleries()->create([
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
    Sanctum::actingAs($user);

    $response = $this->deleteJson(route('api.v1.images.destroy', $image));

    $response->assertNoContent();
    $this->assertModelMissing($image);
    Storage::disk('public')->assertMissing($image->path);
    expect($user->fresh()->storageUsedBytes())->toBe(0);
});

it('returns the current storage usage and quota', function () {
    $user = User::factory()->create(['storage_quota_bytes' => 50 * 1024]);
    $gallery = $user->galleries()->create([
        'name' => 'Portraits',
        'visibility' => 'private',
    ]);
    $gallery->images()->create([
        'path' => 'gallery-images/portrait.jpg',
        'original_name' => 'portrait.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 10 * 1024,
    ]);
    Sanctum::actingAs($user);

    $response = $this->getJson(route('api.v1.auth.me'));

    $response
        ->assertOk()
        ->assertJsonPath('data.storage_used_bytes', 10 * 1024)
        ->assertJsonPath('data.storage_quota_bytes', 50 * 1024)
        ->assertJsonPath('data.storage_remaining_bytes', 40 * 1024);
});
