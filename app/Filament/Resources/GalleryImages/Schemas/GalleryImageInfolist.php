<?php

namespace App\Filament\Resources\GalleryImages\Schemas;

use App\Models\GalleryImage;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GalleryImageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('path')
                    ->label('Image')
                    ->disk(fn(GalleryImage $record): string => $record->disk)
                    ->height(240)
                    ->columnSpanFull(),
                TextEntry::make('gallery.name')->label('Gallery'),
                TextEntry::make('gallery.user.name')->label('Owner'),
                TextEntry::make('original_name'),
                TextEntry::make('embeddings_count')->label('Detected faces'),
                TextEntry::make('processing_status')->badge(),
                IconEntry::make('is_public')->boolean(),
                TextEntry::make('tags')->badge(),
                TextEntry::make('description')->columnSpanFull(),
                TextEntry::make('processing_error')->color('danger')->columnSpanFull(),
                TextEntry::make('processed_at')->dateTime(),
                TextEntry::make('uid')->copyable(),
            ]);
    }
}
