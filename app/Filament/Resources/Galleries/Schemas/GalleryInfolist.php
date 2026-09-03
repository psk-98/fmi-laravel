<?php

namespace App\Filament\Resources\Galleries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GalleryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('user.name')->label('Owner'),
                TextEntry::make('user.email')->label('Owner email'),
                TextEntry::make('visibility')->badge(),
                TextEntry::make('description')->columnSpanFull(),
                TextEntry::make('images_count')->label('Images'),
                TextEntry::make('uid')->copyable(),
                TextEntry::make('created_at')->dateTime(),
            ]);
    }
}
