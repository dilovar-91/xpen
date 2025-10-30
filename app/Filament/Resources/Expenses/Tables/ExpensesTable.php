<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Models\Expense;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        $tiny = ['class' => 'py-1 text-xs']; // <— tiny padding + font

        return $table
            ->header(function ($livewire) {
                return view('filament.expenses.date-filter-inline', [
                    'livewire' => $livewire,
                ]);
            })
            ->columns([
                /* TextColumn::make('manager.name')
                    ->label('Менеджер')
                    ->sortable()
                    ->toggleable()
                    ->extraAttributes($tiny), */

                TextColumn::make('date')
                    ->date()
                    ->label('Дата')
                    ->sortable()
                    ->extraAttributes($tiny),

                TextColumn::make('type_id')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (string|int|null $state): string => match ($state) {
                        1 => 'Приход',
                        2 => 'Расход',
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

                TextColumn::make('income')
                    ->numeric(2)
                    ->label('Приход')
                    ->sortable()
                    ->extraAttributes($tiny),

                TextColumn::make('expense')
                    ->numeric(2)
                    ->label('Расход')
                    ->sortable()
                    ->extraAttributes($tiny),
                TextColumn::make('comment')
                    ->numeric(2)
                    ->label('Комментарий по расходу')
                    ->sortable()
                    ->extraAttributes($tiny),


                TextColumn::make('balance')
                    ->label('Остаток на конец дня')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->extraAttributes($tiny),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->extraAttributes($tiny),
            ])

            ->modifyQueryUsing(function ($query, $livewire) {
                if ($livewire->dateFrom) {
                    $query->whereDate('date', '>=', $livewire->dateFrom);
                }
                if ($livewire->dateTo) {
                    $query->whereDate('date', '<=', $livewire->dateTo);
                }
            })
            ->headerActions([
                Action::make('addIncome')
                    ->label('Добавить приход')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->slideOver()
                    ->form(fn () => self::getExpenseForm(1))
                    ->action(fn (array $data) => \App\Models\Expense::create($data)),

                Action::make('addExpense')
                    ->label('Добавить расход')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('danger')
                    ->slideOver()
                    ->form(fn () => self::getExpenseForm(2))
                    ->action(fn (array $data) => \App\Models\Expense::create($data)),
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
                    ->slideOver()
                    ->modalHeading('Редактирование'),
                DeleteAction::make('delete')
                    ->label('Удалить')
                    ->icon('heroicon-o-trash')
                    ->button()
                    ->size('xs')
                    ->color('danger')


            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->button()->size('xs'),
                ]),
            ])


            ->recordAction('edit')   // откроет EditAction при клике по строке
            ->recordClasses('cursor-pointer')
            ->columnManager(false)
            ->striped()->defaultPaginationPageOption(25);
    }



}
