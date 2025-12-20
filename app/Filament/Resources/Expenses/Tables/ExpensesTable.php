<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Models\Expense;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Form;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpensesTable
{
    public int $showroomId;



    public function mount()
    {
        $this->showroomId = request()->route('showroom');

    }

    public static function configure(Table $table): Table
    {
        $tiny = ['class' => 'py-1 text-xs']; // <— tiny padding + font

        return $table
            ->header(function ($livewire) {
                return view('filament.expenses.date-filter-inline', [
                    'livewire' => $livewire,
                    'allTags' => $livewire->allTags,
                ]);
            })
            ->columns([
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

                TextColumn::make('income_type')
                    ->label('Способ')
                    ->badge()
                    ->formatStateUsing(fn (string|int|null $state): string => match ($state) {
                        1 => 'Наличка',
                        2 => 'Безнал',
                        default => '—',
                    })
                    ->color(fn (string|int|null $state): string => match ($state) {
                        1 => 'warning',
                        2 => 'success',
                        default => 'gray',
                    })
                    ->sortable()
                    ->extraAttributes($tiny),




                TextColumn::make('expense')
                    ->numeric(2)
                    ->label('Расход')
                    ->sortable()
                    ->extraAttributes($tiny),

                TextColumn::make('tags')
                    ->badge()
                    ->sortable()
                    ->columnSpanFull()
                    ->label('Теги'),


                TextColumn::make('comment')
                    ->numeric(2)
                    ->label('Комментарий по расходу')
                    ->sortable()
                    ->extraAttributes($tiny),



                TextColumn::make('remaining_cash')
                    ->numeric(2)
                    ->label('Остаток касса')
                    ->sortable()
                    ->extraAttributes($tiny),

                TextColumn::make('balance')
                    ->numeric(2)
                    ->label('Остаток на конец дня')
                    ->sortable()
                    ->extraAttributes($tiny),



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
                    ->mountUsing(function (Form $form, $livewire) {

                        $showroomId = $livewire->showroomId ?? request()->route('showroom') ?? request()->query('showroom_id');


                        $form->schema(
                            ExpenseResource::getExpenseForm(1, $showroomId)
                        );
                    })
                    ->action(fn (array $data) => Expense::create($data)),

                Action::make('addExpense')
                    ->label('Добавить расход')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('danger')
                    ->slideOver()
                    ->mountUsing(function (Form $form, $livewire) {
                        $showroomId = $livewire->showroomId ?? request()->route('showroom_id');

                        $form->schema(
                            ExpenseResource::getExpenseForm(2, $showroomId)
                        );
                    })
                    ->action(fn (array $data) => Expense::create($data)),
            ])
            ->recordClasses(fn ($record) => $record->type_id == 1
                ? 'bg-success-100 dark:bg-success-900/40'
                : 'bg-danger-100 dark:bg-danger-900/40')
            ->filters([])
            ->recordActions([

                Action::make('accept')
                    ->label(fn ($record) => $record->accepted == 1
                        ? 'Принято'
                        : 'Принять')
                    ->icon('heroicon-o-check-circle')
                    ->button()
                    ->size('xs')
                    ->color(fn ($record) => $record->accepted == 1
                        ? 'success'
                        : 'danger')
                    ->visible(fn ($record) =>
                        auth()->user()?->role === 'admin'
                    )
                    ->action(fn ($record) => $record->update([
                        'accepted' => 1,
                    ]))
                    ->disabled(fn ($record) => $record->accepted === 1)
                    ->requiresConfirmation(),

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
            ->striped()->defaultPaginationPageOption(25);
    }



}
