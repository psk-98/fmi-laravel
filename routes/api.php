<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\RegisterUserController;
use App\Http\Controllers\Api\V1\Gallery\GalleryController;
use App\Http\Controllers\Api\V1\Gallery\GalleryImageController;
use App\Http\Controllers\Api\V1\Gallery\MeGalleriesController;
use App\Http\Controllers\Api\V1\Gallery\MeGalleryImagesController;
use App\Http\Controllers\Api\V1\Processor\ImageResultController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/login', LoginController::class)->middleware('throttle:login')->name('auth.login');
    Route::post('auth/register', RegisterUserController::class)->name('auth.register');

    Route::get('galleries', [GalleryController::class, 'index'])->name('galleries.index');
    Route::get('galleries/{gallery}', [GalleryController::class, 'show'])->name('galleries.show');
    Route::get('galleries/{gallery}/images', [GalleryImageController::class, 'index'])->name('galleries.images.index');
    Route::get('images/{galleryImage}', [GalleryImageController::class, 'show'])->name('images.show');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
        Route::get('auth/me', MeController::class)->name('auth.me');
        Route::post('auth/logout', LogoutController::class)->name('auth.logout');
        Route::get('me/galleries', MeGalleriesController::class)->name('galleries.mine');
        Route::get('me/galleries/{gallery}', MeGalleryImagesController::class)->name('galleries.owned');
        Route::post('galleries', [GalleryController::class, 'store'])->name('galleries.store');
        Route::patch('galleries/{gallery}', [GalleryController::class, 'update'])->name('galleries.update');
        Route::delete('galleries/{gallery}', [GalleryController::class, 'destroy'])->name('galleries.destroy');
        Route::post('galleries/{gallery}/images', [GalleryImageController::class, 'store'])->name('galleries.images.store');
        Route::delete('images/{galleryImage}', [GalleryImageController::class, 'destroy'])->name('images.destroy');
        // Route::post('images/search', ImageSearchController::class)->name('images.search');
    });


    Route::patch('processor/images/{galleryImage}', ImageResultController::class)
        ->middleware(['image.processor', 'throttle:processor'])
        ->name('processor.images.update');
});
