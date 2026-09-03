<?php

namespace App\Filament\Resources\Galleries\Tables;

use App\Models\Gallery;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GalleriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('user.name')->label('Owner')->searchable()->sortable(),
                TextColumn::make('user.email')->label('Owner email')->searchable()->toggleable(),
                TextColumn::make('visibility')->badge()->sortable(),
                TextColumn::make('images_count')->counts('images')->label('Images')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('visibility')->options([
                    Gallery::VISIBILITY_PRIVATE => 'Private',
                    Gallery::VISIBILITY_PUBLIC => 'Public',
                ]),
            ])
            ->searchable(['uid', 'description', 'user.name', 'user.email'])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
