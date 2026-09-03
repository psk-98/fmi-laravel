<?php

namespace App\Filament\Resources\GalleryImages;

use App\Filament\Resources\GalleryImages\Pages\EditGalleryImage;
use App\Filament\Resources\GalleryImages\Pages\ListGalleryImages;
use App\Filament\Resources\GalleryImages\Pages\SimilarGalleryImages;
use App\Filament\Resources\GalleryImages\Pages\ViewGalleryImage;
use App\Filament\Resources\GalleryImages\Schemas\GalleryImageForm;
use App\Filament\Resources\GalleryImages\Schemas\GalleryImageInfolist;
use App\Filament\Resources\GalleryImages\Tables\GalleryImagesTable;
use App\Models\GalleryImage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GalleryImageResource extends Resource
{
    protected static ?string $model = GalleryImage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'original_name';

    public static function form(Schema $schema): Schema
    {
        return GalleryImageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GalleryImageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GalleryImagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /** @return array<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'celebrity_name',
            'original_name',
            'description',
            'uid',
            'gallery.name',
            'gallery.user.name',
            'gallery.user.email',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->celebrity_name ?: ($record->original_name ?: $record->uid);
    }

    /** @return array<string, string> */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Owner' => $record->gallery->user->name,
            'Gallery' => $record->gallery->name,
        ];
    }

    /** @return Builder<GalleryImage> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('gallery.user')
            ->withCount('embeddings');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleryImages::route('/'),
            'view' => ViewGalleryImage::route('/{record}'),
            'edit' => EditGalleryImage::route('/{record}/edit'),
            'similar' => SimilarGalleryImages::route('/{record}/similar'),
        ];
    }
}
