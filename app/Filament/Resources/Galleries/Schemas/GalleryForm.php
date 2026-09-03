<?php

namespace App\Filament\Resources\Galleries\Schemas;

use App\Models\Gallery;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GalleryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->required()
                    ->maxLength(2000)
                    ->columnSpanFull(),
                Select::make('visibility')
                    ->options([
                        Gallery::VISIBILITY_PRIVATE => 'Private',
                        Gallery::VISIBILITY_PUBLIC => 'Public',
                    ])
                    ->required()
                    ->default(Gallery::VISIBILITY_PRIVATE),
            ]);
    }
}
