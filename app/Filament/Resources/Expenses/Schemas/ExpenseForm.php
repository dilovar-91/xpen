<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type_id')
                    ->label('Тип операции')
                    ->options([
                        1 => 'Приход',
                        2 => 'Расход',
                    ])
                    ->reactive() // 👈 обязательно для динамического поведения
                    ->default(1)
                    ->afterStateUpdated(fn (callable $set) => $set('income', null))
                    ->afterStateUpdated(fn (callable $set) => $set('expense', null)),
                Select::make('manager_id')
                    ->relationship('manager', 'name')
                    ->label('Менеджер'),
                Select::make('showroom_id')
                    ->relationship('showroom', 'name')
                    ->label('Салон')
                    ->required(),
                DatePicker::make('date')
                    ->label('Дата')
                    ->required(),
                TextInput::make('income')
                    ->required()
                    ->label('Приход')
                    ->reactive()
                    ->disabled(fn (callable $get) => $get('type_id') === 2)
                    ->numeric(),
                TextInput::make('expense')
                    ->required()
                    ->label('Расход')
                    ->reactive()
                    ->disabled(fn (callable $get) => $get('type_id') === 1)
                    ->numeric(),
                TextInput::make('balance')
                    ->label('Остаток на конец дня')
                    ->required()
                    ->numeric(),
                Textarea::make('comment')
                    ->label('Комментарий')
                    ->columnSpanFull(),
            ]);
    }
}
