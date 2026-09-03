<?php

namespace App\Filament\Resources\GalleryImages\Schemas;

use App\ModerationStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GalleryImageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('celebrity_name')
                    ->label('Celebrity')
                    ->maxLength(255),
                Toggle::make('is_public')
                    ->label('Visible in public API'),
                TagsInput::make('tags')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->maxLength(2000)
                    ->columnSpanFull(),
            ]);
    }
}
