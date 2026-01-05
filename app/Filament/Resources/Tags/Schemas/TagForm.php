<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->unique(ignoreRecord: true),

                     Select::make('type_id')
                    ->label('Тип')
                    ->required()
                     ->columnSpanFull()
                    ->options([
                        1 => 'Приход',
                        2 => 'Расход',
                    ])
                    ->native(false), // красивый select (опционально)
            ]);
    }
}
