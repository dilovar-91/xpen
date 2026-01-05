<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Checkbox;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;

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
                     ->disabled()
                     ->afterStateUpdated(fn (callable $set) => $set('income', null))
                     ->afterStateUpdated(fn (callable $set) => $set('expense', null)), /*
                 Select::make('manager_id')
                     ->relationship('manager', 'name')
                     ->label('Менеджер'),
                 Select::make('showroom_id')
                     ->relationship('showroom', 'name')
                     ->label('Салон')
                     ->required(),*/
                DatePicker::make('date')
                    ->label('Дата')
                    ->required(),
                TextInput::make('income')
                    ->required()
                    ->label('Приход')
                    ->reactive()
                    ->disabled(fn(callable $get) => $get('type_id') === 2)
                    ->numeric(),

                Select::make('income_type')->label('Тип прихода')->options([1 => 'Наличка', 2 => 'Безнал',])->visible(fn(callable $get) => $get('type_id') === 1)->required(fn(callable $get) => $get('type_id') === 1),


                TextInput::make('expense')
                    ->required()
                    ->label('Расход')
                    ->reactive()
                    ->disabled(fn(callable $get) => $get('type_id') === 1)
                    ->numeric(),


                TextInput::make('remaining_cash')
                    ->required()
                    ->label('Остаток касса')
                    ->reactive()
                    ->numeric(),
                Select::make('tag_id')
                ->label('Тег')
                ->relationship(
                    name: 'tag',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query, Get $get) =>
                        $query->where('type_id', $get('type_id')),
                )
                ->searchable()
                ->preload()
                ->required()
                ->placeholder('Выберите тег')
                ->reactive(),

                Textarea::make('comment')
                    ->label('Комментарий')
                    ->columnSpanFull(),

                Checkbox::make('auto_calculate')
                    ->label('Отключить авторасчет')
                    ->visible(false)
                    ->reactive(),
            ]);
    }
}
