<?php

namespace App\Filament\Resources\Receipts\Tables;

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Filament\Resources\Receipts\ReceiptResource;
use App\Models\Receipt;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Form;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use NumberFormatter;

class ReceiptsTable
{
    public static function configure(Table $table): Table
    {

        $tiny = ['class' => 'py-1 text-xs'];
        return $table
            ->header(function ($livewire) {
                return view('filament.receipt.date-filter-inline', [
                    'livewire' => $livewire,
                ]);
            })
            ->columns([

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Дата создание')
                    ->sortable()
                    ->extraAttributes($tiny),

                TextColumn::make('full_name')
                    ->label('ФИО')
                    ->sortable()
                    ->extraAttributes($tiny),

                TextColumn::make('phone')
                    ->label('Телефон')
                    ->sortable()
                    ->extraAttributes($tiny),


                TextColumn::make('car_mark')
                    ->label('Марка')
                    ->sortable()
                    ->extraAttributes($tiny),
                TextColumn::make('car_model')
                    ->label('Модель')
                    ->sortable()
                    ->extraAttributes($tiny),
                TextColumn::make('vin_number')
                    ->label('VIN номер')
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
                    ->formatStateUsing(fn(string|int|null $state): string => match ($state) {
                        1 => 'Частичная',
                        2 => 'Полная',
                        default => '—',
                    })
                    ->color(fn(string|int|null $state): string => match ($state) {
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




                TextColumn::make('full_price')
                    ->numeric(0, thousandsSeparator: ' ')
                    ->formatStateUsing(function ($state) {
                        if ($state === null) return null;

                        $fmt = new NumberFormatter('ru_RU', NumberFormatter::CURRENCY);
                        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

                        return $fmt->formatCurrency((float) $state, 'RUB');
                    })
                    ->label('Полная сумма')
                    ->sortable()
                    ->extraAttributes($tiny),
                TextColumn::make('closed')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Закрыта' : 'Открыта')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),

                // 🔽 Вложенные ReceiptItem
                ViewColumn::make('items')
                    ->label('Оплаты')
                    ->view('filament.tables.receipt-items')
                    ->extraAttributes(['class' => 'p-0']),

                TextColumn::make('file')
                    ->label('Файл')
                    ->formatStateUsing(fn ($state) => $state ? 'Скачать' : '-')
                    ->url(fn ($record) => $record->file
                        ? asset('storage/' . $record->file)
                        : null
                    )
                    ->openUrlInNewTab()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('comment')
                    ->numeric(2)
                    ->label('Комментарий')
                    ->sortable()
                    ->extraAttributes($tiny),


            ])
            ->modifyQueryUsing(function ($query, $livewire) {
                if ($livewire->dateFrom) {
                    $query->whereDate('created_at', '>=', $livewire->dateFrom);
                }
                if ($livewire->dateTo) {
                    $query->whereDate('created_at', '<=', $livewire->dateTo);
                }

                if ($livewire->type) {
                    $query->where('type_id', $livewire->type);
                }
            })

            ->recordClasses(fn ($record) => [
                'bg-green-500 text-white' => $record->approved === true,
                //'bg-red-500' => $record->approved === false,
            ])
            ->filters([])
            ->recordActions([

                Action::make('accept')
                    ->label(fn ($record) => $record->approved == 1
                        ? 'Принятa'
                        : 'Принять')
                    ->icon('heroicon-o-check-circle')
                    ->button()
                    ->size('xs')
                    ->color(fn ($record) => $record->approved == 1
                        ? 'success'
                        : 'danger')
                    ->visible(fn ($record) =>
                        auth()->user()?->role === 'admin'
                    )
                    ->action(function ($record) {
                        $record->update(['approved' => 1]);
                    })
                    ->disabled(fn ($record) => $record->approved === 1)
                    ->requiresConfirmation(),

                EditAction::make('edit')
                    ->label('Изменить')
                    ->icon('heroicon-o-pencil-square')
                    ->button()
                    ->size('xs')
                    ->visible(fn () => auth()->user()?->role !== 'guest')
                    ->slideOver()
                    ->color('warning')
                    ->visible(fn() => auth()->user()?->role === 'admin')
                    ->modalHeading('Редактирование'),

                EditAction::make('goto')
                    ->label('К оплатам')
                    ->icon('heroicon-o-pencil-square')
                    ->button()
                    ->size('xs')
                    ->color('success')

                    //->visible(fn() => auth()->user()?->role === 'admin')
                    ->url(fn ($record) => ExpenseResource::getUrl(
                        'showroom-receipt-detail',
                        ['showroom'=>$record->showroom_id, 'id' => $record->id]
                    ))
                    ->modalHeading('Редактирование'),
                DeleteAction::make('delete')
                    ->label('Удалить')
                    ->icon('heroicon-o-trash')
                    ->button()
                    ->visible(fn() => auth()->user()?->role === 'admin')
                    ->size('xs')
                    ->color('danger')


            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->button()->size('xs')->visible(fn() => auth()->user()?->role === 'admin'),
                ]),
            ])
            ->recordAction('edit')   // откроет EditAction при клике по строке

            ->columnManager(false)
            ->striped()->defaultPaginationPageOption(30);
    }
}
