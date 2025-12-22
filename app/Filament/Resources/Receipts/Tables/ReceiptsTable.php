<?php

namespace App\Filament\Resources\Receipts\Tables;

use App\Filament\Resources\Receipts\ReceiptResource;
use App\Models\Receipt;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class ReceiptsTable
{
    public static function configure(Table $table): Table
    {

        $tiny = ['class' => 'py-1 text-xs'];
        return $table
            ->groups([
                Group::make('group_id')
                    ->label('Группа')
                    ->getTitleFromRecordUsing(function ($record) {
                        if ($record->group_id) {
                            // Это дочерний чек, ищем родителя
                            $parent = \App\Models\Receipt::find($record->group_id);
                            return $parent
                                ? "Родитель: {$parent->full_name}, {$parent->phone} (ID #{$parent->id})"
                                : "Группа #{$record->group_id}";
                        }
                    })
            ])
            ->defaultGroup('group_id')
            ->groupingSettingsHidden() // нельзя отключить группировку
            ->columns([

                TextColumn::make('full_name')
                    ->label('ФИО')
                    ->sortable()
                    ->extraAttributes($tiny),

                TextColumn::make('phone')
                    ->label('Телефон')
                    ->sortable()
                    ->extraAttributes($tiny),

                TextColumn::make('repayment_date')
                    ->date()
                    ->label('Дата возвращение')
                    ->sortable()
                    ->extraAttributes($tiny),

                TextColumn::make('type_id')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (string|int|null $state): string => match ($state) {
                        1 => 'Частичная',
                        2 => 'Полная',
                        default => '—',
                    })
                    ->color(fn (string|int|null $state): string => match ($state) {
                        1 => 'success',
                        2 => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->extraAttributes($tiny),




                TextColumn::make('showroom.name')
                    ->label('Салон')
                    ->sortable()
                    ->toggleable()
                    ->extraAttributes($tiny),

                TextColumn::make('part_price')
                    ->numeric(2)
                    ->label('Частичная сумма')
                    ->money('RUB', true)
                    ->sortable()
                    ->extraAttributes($tiny),


                TextColumn::make('full_price')
                    ->numeric(2)
                    ->money('RUB', true)
                    ->label('Полная сумма')
                    ->sortable()
                    ->extraAttributes($tiny),




                TextColumn::make('comment')
                    ->numeric(2)
                    ->label('Комментарий')
                    ->sortable()
                    ->extraAttributes($tiny),

            ])
            ->recordClasses(fn ($record) => $record->type_id == 1
                ? 'bg-success-100 dark:bg-success-900/40'
                : 'bg-danger-100 dark:bg-danger-900/40')
            ->filters([])
            ->recordActions([

                EditAction::make('edit')
                    ->label('Изменить')
                    ->icon('heroicon-o-pencil-square')
                    ->button()
                    ->size('xs')
                    ->color('success')
                    ->visible(fn () => auth()->user()?->role === 'admin')
                    ->slideOver()
                    ->modalHeading('Редактирование'),
                DeleteAction::make('delete')
                    ->label('Удалить')
                    ->icon('heroicon-o-trash')
                    ->button()
                    ->visible(fn () => auth()->user()?->role === 'admin')
                    ->size('xs')
                    ->color('danger')


            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->button()->size('xs')->visible(fn () => auth()->user()?->role === 'admin'),
                ]),
            ])
            ->recordAction('edit')   // откроет EditAction при клике по строке
            ->recordClasses('cursor-pointer')
            ->columnManager(false)
            ->striped()->defaultPaginationPageOption(30);
    }
}
