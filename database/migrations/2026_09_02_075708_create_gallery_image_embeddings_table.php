<?php

use App\Models\GalleryImage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const EMBEDDING_DIMENSIONS = 512;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('gallery_image_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(GalleryImage::class)->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('face_index')->default(0);
            $table->string('model')->default('clip-vit-base-patch32')->index();
            $table->unsignedSmallInteger('dimensions')->default(self::EMBEDDING_DIMENSIONS);
            $table->json('metadata')->nullable();
            $table->vector('embedding', dimensions: self::EMBEDDING_DIMENSIONS)->nullable()->index();
            $table->json('bounding_box')->nullable();
            $table->double('detection_score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_image_embeddings');
    }
};
