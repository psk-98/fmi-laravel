<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Domain\User\Enums\UserRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
                Select::make('role')
                    ->options(UserRole::class)
                    ->required()
                    ->default(UserRole::User),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(fn(?string $state): bool => filled($state))
                    ->maxLength(255),

            ]);
    }
}
