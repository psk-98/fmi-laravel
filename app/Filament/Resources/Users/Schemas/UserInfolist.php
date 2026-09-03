<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('role')
                    ->badge(),
                TextEntry::make('galleries_count')
                    ->label('Galleries'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
