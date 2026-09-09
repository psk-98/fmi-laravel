<?php

namespace App\Filament\Resources\GalleryImages\Pages;

use App\Filament\Resources\GalleryImages\GalleryImageResource;
use App\Models\GalleryImage;
use App\Models\User;
use App\Services\ImageSimilaritySearch;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class SimilarGalleryImages extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = GalleryImageResource::class;

    protected string $view = 'filament.resources.gallery-images.similar-gallery-images';

    private ImageSimilaritySearch $similaritySearch;

    public function boot(ImageSimilaritySearch $similaritySearch): void
    {
        $this->similaritySearch = $similaritySearch;
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless(GalleryImageResource::canView($this->getRecord()), 403);
    }

    public function getTitle(): string
    {
        return 'Images similar to '.$this->getRecordTitle();
    }

    public function table(Table $table): Table
    {
        $image = $this->getRecord();
        $user = Filament::auth()->user();

        abort_unless($image instanceof GalleryImage && $user instanceof User, 403);

        $image->loadMissing('embeddings');
        $embeddings = $image->embeddings->pluck('embedding')->all();
        $query = $this->similaritySearch->queryMany(
            embeddings: $embeddings,
            user: $user,
            includePrivate: $user->isAdmin(),
            excludeImageId: $image->id,
        );

        return $table
            ->query($query)
            ->columns([
                ImageColumn::make('path')
                    ->label('Image')
                    ->disk(fn (GalleryImage $record): string => $record->disk)
                    ->square(),
                TextColumn::make('gallery.user.name')->label('Owner'),
                TextColumn::make('gallery.name')->label('Gallery'),
                TextColumn::make('similarity')
                    ->label('Match')
                    ->formatStateUsing(fn (float $state): string => number_format($state * 100, 2).'%')
                    ->sortable(),
            ])
            ->recordUrl(fn (GalleryImage $record): string => GalleryImageResource::getUrl('view', ['record' => $record]))
            ->paginated([10, 25, 50]);
    }
}
