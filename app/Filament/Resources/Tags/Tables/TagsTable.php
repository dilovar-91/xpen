<?php

namespace App\Filament\Resources\Tags\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('№')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                    TextColumn::make('type_id')
                    ->label('Тип')
                    ->formatStateUsing(fn (int $state) => match ($state) {
                        1 => 'Приход',
                        2 => 'Расход',
                        default => '—',
                    })
                    ->badge()
                    ->color(fn (int $state) => match ($state) {
                        1 => 'success',
                        2 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('expenses_count')
                    ->label('Используется')
                    ->counts('expenses')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->slideover(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Добавить тег')
                ->icon('heroicon-o-plus')
                ->slideover(),
        ];
    }
}
