<?php

namespace App\Filament\Resources\Showrooms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ShowroomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
