<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Models\CashDailyBalance;
use App\Models\Expense;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Form;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use NumberFormatter;

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
            ->defaultSort('id', 'asc')
            ->defaultGroup('date')          // ← всегда группировать по дате
            ->groupingSettingsHidden()      // ← скрыть выбор группировки
            ->groups([
                Group::make('date')
                    ->label('Дата')
                    ->getTitleFromRecordUsing(fn ($record) =>
                    $record->date->format('d.m.Y')
                    )
                    ->getDescriptionFromRecordUsing(function ($record) {

                        $balance = CashDailyBalance::whereDate('date', $record->date)
                            ->where('showroom_id', $record->showroom_id)
                            ->first();

                        $valueNumber = $balance ? (float) $balance->closing_balance : null;

                        $valueFormatted = $balance
                            ? number_format($balance->closing_balance, 0, '', ' ') . ' ₽'
                            : 'Остаток не найден';

                        $date = $record->date->toDateString();
                        $showroomId = (int) $record->showroom_id;

                        // Для передачи в JS / Livewire лучше передавать 0 или null
                        $valueForJs = $valueNumber ?? 'null';

                        return new HtmlString("
                            <div class='flex items-center justify-between gap-2'>
                                <div>
                                    <span class='text-gray-600'>Остаток на конец дня:</span>
                                    <span class='font-semibold'>{$valueFormatted}</span>
                                </div>

                                <button
                                    type='button'
                                    class='text-primary-600 hover:underline text-sm'
                                    wire:click=\"mountTableAction(
                                        'editClosingBalance',
                                        null,
                                        { date: '{$date}', showroom_id: {$showroomId}, closing_balance: {$valueForJs} }
                                    )\"
                                >
                                    Изменить
                                </button>
                            </div>
                        ");
                    })
            ])
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
                    ->formatStateUsing(function ($state) {
                        if ($state === null) return null;

                        $fmt = new NumberFormatter('ru_RU', NumberFormatter::CURRENCY);
                        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

                        return $fmt->formatCurrency((float) $state, 'RUB');
                    })
                    ->sortable()
                    ->extraAttributes($tiny),

                TextColumn::make('income_type')
                    ->label('Способ оплаты')
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
                    ->formatStateUsing(function ($state) {
                        if ($state === null) return null;

                        $fmt = new NumberFormatter('ru_RU', NumberFormatter::CURRENCY);
                        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

                        return $fmt->formatCurrency((float) $state, 'RUB');
                    })
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
                    ->formatStateUsing(function ($state) {
                        if ($state === null) return null;

                        $fmt = new NumberFormatter('ru_RU', NumberFormatter::CURRENCY);
                        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

                        return $fmt->formatCurrency((float) $state, 'RUB');
                    })
                    ->sortable()
                    ->extraAttributes($tiny),


                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
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

                Action::make('editClosingBalance')
                    ->label('Изменить остаток')
                    ->hiddenLabel()

                    ->modalHeading(function (Action $action) {
                        $args = $action->getArguments();

                        return 'Изменить остаток на конец дня: ' .
                            \Carbon\Carbon::parse($args['date'])->format('d.m.Y');
                    })
                    ->modalSubmitActionLabel('Сохранить')
                    ->fillForm(function (array $arguments) {
                        return [
                            'closing_balance' => $arguments['closing_balance']
                                ?? CashDailyBalance::whereDate('date', $arguments['date'])
                                    ->where('showroom_id', $arguments['showroom_id'])
                                    ->value('closing_balance') ?? 0,
                        ];
                    })
                    ->schema([
                        TextInput::make('closing_balance')
                            ->label('Остаток на конец дня')
                            ->numeric()
                            ->required()
                            ->default(function (Action $action) {
                                $args = $action->getArguments();
                                //dd($args);
                                return $args['closing_balance'];

                                // ✅ 1) если передали значение из группы — используем его
                                if (array_key_exists('closing_balance', $args) && $args['closing_balance'] !== null) {
                                    return (float) $args['closing_balance'];
                                }

                                // ✅ 2) иначе — берём из БД
                                return CashDailyBalance::whereDate('date', $args['date'] ?? null)
                                    ->where('showroom_id', $args['showroom_id'] ?? null)
                                    ->value('closing_balance') ?? 0;
                            }),
                    ])
                    ->action(function (Action $action, array $data) {
                        $args = $action->getArguments();

                        CashDailyBalance::updateOrCreate(
                            [
                                'date' => $args['date'],
                                'showroom_id' => $args['showroom_id'],
                            ],
                            [
                                'closing_balance' => (float) $data['closing_balance'],
                                'manually_changed'  => 1,
                            ]
                        );
                    })
                    ->successNotificationTitle('Остаток обновлён'),
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
                    ->action(function ($record) {
                        // 1️⃣ Обновляем accepted у операции
                        $record->update(['accepted' => 1]);

                        // 2️⃣ Определяем дату и кассу (showroom)
                        $date = $record->date;
                        $showroomId = $record->showroom_id;

                        // 3️⃣ Берём все операции за день для этого шоурума
                        $operations = Expense::whereDate('date', $date)
                            ->where('showroom_id', $showroomId)
                            ->where('online_cash', '<=', '0')
                            ->get();

                        $dailyBalance = CashDailyBalance::query()
                            ->whereDate('date', $date)
                            ->where('showroom_id', $showroomId)
                            ->first();

                        if ($dailyBalance?->manually_changed) {

                            // есть ли операции после updated_at баланса?
                            $hasOperationsAfterManual = Expense::query()
                                ->whereDate('date', $date)
                                ->where('showroom_id', $showroomId)
                                ->where('online_cash', '<=', 0)
                                ->where(function ($q) use ($dailyBalance) {
                                    $q->where('created_at', '>', $dailyBalance->updated_at)
                                        ->orWhere('updated_at', '>', $dailyBalance->updated_at);
                                })
                                ->exists();

                            // ✅ Если после ручного изменения операций не было — ничего не пересчитываем
                            if (! $hasOperationsAfterManual) {
                                return;
                            }

                            // ✅ Если операции были — снимаем ручной режим, чтобы пересчитать
                            $dailyBalance->update([
                                'manually_changed' => 0,
                            ]);
                        }

                        if ($operations->isNotEmpty()) {
                            // 4️⃣ Считаем opening_balance по предыдущему дню
                            $openingBalance = CashDailyBalance::where('showroom_id', $showroomId)
                                ->whereDate('date', '<', $date)
                                ->orderBy('date', 'desc')
                                ->value('closing_balance') ?? 0;

                            // 5️⃣ Считаем closing_balance = opening + SUM(income) - SUM(expense)
                            $totalIncome = $operations->sum('income');
                            $totalExpense = $operations->sum('expense');

                            $closingBalance = $openingBalance + $totalIncome - $totalExpense;

                            // 6️⃣ Сохраняем или обновляем запись в cash_daily_balances
                            CashDailyBalance::updateOrCreate(
                                [
                                    'date' => $date,
                                    'showroom_id' => $showroomId
                                ],
                                [
                                    'opening_balance' => $openingBalance,
                                    'closing_balance' => $closingBalance,
                                    'approved' => true
                                ]
                            );

                            // 7️⃣ Обновляем remaining_cash у каждой операции (опционально)
                            $currentBalance = $openingBalance;
                            foreach ($operations as $op) {
                                $currentBalance += $op->income - $op->expense;
                                $op->update(['remaining_cash' => $currentBalance]);
                            }
                        }
                    })
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
