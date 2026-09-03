<?php

namespace App\Filament\Resources\GalleryImages\Tables;

use App\Domain\Gallery\Enums\ProcessingStatus;
use App\Filament\Resources\GalleryImages\GalleryImageResource;
use App\Jobs\ProcessGalleryImage;
use App\Models\GalleryImage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GalleryImagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('Image')
                    ->disk(fn(GalleryImage $record): string => $record->disk)
                    ->square(),
                TextColumn::make('gallery.name')->searchable()->sortable(),
                TextColumn::make('gallery.user.name')->label('Owner')->searchable()->sortable(),
                TextColumn::make('embeddings_count')->label('Faces')->sortable(),
                TextColumn::make('processing_status')->badge()->sortable(),
                IconColumn::make('is_public')->boolean()->label('Public'),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('processing_status')->options(ProcessingStatus::class),
            ])
            ->searchable([
                'uid',
                'original_name',
                'description',
                'tags',
                'gallery.name',
                'gallery.user.name',
                'gallery.user.email',
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('similar')
                    ->label('Find similar')
                    ->icon(Heroicon::OutlinedMagnifyingGlass)
                    ->url(fn(GalleryImage $record): string => GalleryImageResource::getUrl('similar', ['record' => $record]))
                    ->visible(fn(GalleryImage $record): bool => $record->embeddings_count > 0),
                Action::make('reprocess')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->requiresConfirmation()
                    ->action(function (GalleryImage $record): void {
                        $record->update([
                            'processing_status' => ProcessingStatus::Pending,
                            'processing_error' => null,
                        ]);
                        ProcessGalleryImage::dispatch($record->id);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
