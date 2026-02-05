<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Models\CashDailyBalance;
use App\Models\Expense;
use Carbon\Carbon;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use NumberFormatter;
use Illuminate\Support\Facades\Auth;

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


                    ->getTitleFromRecordUsing(fn($record) => $record->date->format('d.m.Y')
                    )

                    ->getDescriptionFromRecordUsing(function ($record) {

                        // 🔹 Текущий день
                        $balanceToday = CashDailyBalance::whereDate('date', $record->date)
                            ->where('showroom_id', $record->showroom_id)
                            ->first();

                        $todayValue = $balanceToday
                            ? number_format($balanceToday->closing_balance, 0, '', ' ') . ' ₽'
                            : 'Не найден';

                        $todayValueNumber = $balanceToday
                            ? (float)$balanceToday->closing_balance
                            : null;

                        $balancePrev = CashDailyBalance::query()
                            ->where('showroom_id', $record->showroom_id)
                            ->whereDate('date', '<', $record->date)
                            ->orderByDesc('date')
                            ->first();

                        $prevValue = $balancePrev
                            ? number_format((float) $balancePrev->closing_balance, 0, '', ' ') . ' ₽'
                            : '0 ₽';

                        // 🔹 Для кнопок
                        $date = $record->date->toDateString();
                        $showroomId = (int)$record->showroom_id;
                        $valueForJs = $todayValueNumber !== null ? $todayValueNumber : 'null';

                        //$isAdmin = auth()->user()?->role === 'admin' ?? true;
                        $isAdmin = true;

                        $editButtonHtml = $isAdmin
                            ? "
            <button
                type='button'
                class='fi-color fi-color-danger fi-bg-color-600 hover:fi-bg-color-500 dark:fi-bg-color-600 dark:hover:fi-bg-color-500 fi-text-color-0 hover:fi-text-color-0 dark:fi-text-color-0 dark:hover:fi-text-color-0 fi-btn fi-size-xs  fi-ac-btn-action'
                wire:click=\"mountTableAction(
                    'editClosingBalance',
                    null,
                    { date: '{$date}', showroom_id: {$showroomId}, closing_balance: {$valueForJs} }
                )\"
            >
                Изменить
            </button>
        "
                            : '';

                       $formattedData =  Carbon::parse($date)->format('d.m.Y');

                        // ✅ Новая кнопка: Принять за дату
                        $acceptDateHtml = $isAdmin
                            ? "
            <button
                type='button'
                class='ml-10 fi-color fi-color-success fi-bg-color-400 hover:fi-bg-color-300 dark:fi-bg-color-600 dark:hover:fi-bg-color-500 fi-text-color-900 hover:fi-text-color-800 dark:fi-text-color-950 dark:hover:fi-text-color-950 fi-btn fi-size-xs  fi-ac-btn-action'
                wire:click=\"mountTableAction(
                    'acceptDay',
                    null,
                    { date: '{$date}', showroom_id: {$showroomId} }
                )\"
            >
                Принять за дату {$formattedData}
            </button>
        "
                            : '';

                        return new \Illuminate\Support\HtmlString("
        <div class='flex flex-col gap-1'>
            <div class='flex items-center justify-between gap-2'>
                <div>
                    <span class='text-gray-600'>Остаток на конец дня:</span>
                    <span class='font-semibold'>{$todayValue}</span>
                </div>

                <div class='flex items-center gap-2'>
                    {$editButtonHtml}
                     {$acceptDateHtml}
                </div>
            </div>

            <div class='text-sm text-gray-500'>
                Остаток предыдущего дня:
                <span class='font-medium'>{$prevValue}</span>
            </div>
        </div>
    ");

                    })
            ])
            ->header(function ($livewire) {
                return view('filament.expenses.date-filter-inline', [
                    'livewire' => $livewire,
                ]);
            })
            ->defaultPaginationPageOption(100)
            ->paginationPageOptions([25, 50, 100, 200])
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->label('Дата')
                    ->sortable()
                    ->extraAttributes($tiny),

                TextColumn::make('type_id')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn(string|int|null $state): string => match ($state) {
                        1 => 'Приход',
                        2 => 'Расход',
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

                TextColumn::make('income')
                    ->numeric(2)
                    ->label('Приход')
                    ->formatStateUsing(function ($state) {
                        if ($state === null) return null;

                        $fmt = new NumberFormatter('ru_RU', NumberFormatter::CURRENCY);
                        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

                        return $fmt->formatCurrency((float)$state, 'RUB');
                    })
                    ->sortable()
                    ->extraAttributes($tiny),

                TextColumn::make('income_type')
                    ->label('Способ оплаты')
                    ->badge()
                    ->formatStateUsing(fn(string|int|null $state): string => match ($state) {
                        1 => 'Наличка',
                        2 => 'Безнал',
                        default => '—',
                    })
                    ->color(fn(string|int|null $state): string => match ($state) {
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

                        return $fmt->formatCurrency((float)$state, 'RUB');
                    })
                    ->sortable()
                    ->extraAttributes($tiny),

                TextColumn::make('tag.name')
                    ->label('Тег')
                    ->badge()
                    ->sortable()
                    ->color('danger'),


                TextColumn::make('comment')
                    ->label('Комментарий по расходу')
                    ->sortable()
                    ->wrap()
                    ->extraAttributes([
                        ...$tiny,
                        'class' => 'max-w-[350px] whitespace-normal break-words',
                    ]),

                TextColumn::make('remaining_cash')
                    ->numeric(2)
                    ->label('Остаток касса')
                    ->formatStateUsing(function ($state) {
                        if ($state === null) return null;

                        $fmt = new NumberFormatter('ru_RU', NumberFormatter::CURRENCY);
                        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

                        return $fmt->formatCurrency((float)$state, 'RUB');
                    })
                    ->sortable()
                    ->extraAttributes($tiny),


                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
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
                    ->action(fn(array $data) => Expense::create($data)),

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
                    ->action(fn(array $data) => Expense::create($data)),
            ])
            ->recordClasses(fn($record) => $record->type_id == 1
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
                            Carbon::parse($args['date'])->format('d.m.Y');
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
                                    return (float)$args['closing_balance'];
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
                                'closing_balance' => (float)$data['closing_balance'],
                                'manually_changed' => 1,
                            ]
                        );
                    })
                    ->successNotificationTitle('Остаток обновлён'),

                Action::make('acceptDay')
                    ->hiddenLabel()
                    ->label('Принять день')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn () => auth()->user()?->role === 'admin')
                    ->action(function (array $arguments) {

                        $date = $arguments['date'] ?? null;
                        $showroomId = (int)($arguments['showroom_id'] ?? 0);

                        if (! $date || ! $showroomId) {
                            return;
                        }

                        DB::transaction(function () use ($date, $showroomId) {

                            /** 1) Принимаем все операции дня (которые ещё не приняты) */
                            Expense::query()
                                ->whereDate('date', $date)
                                ->where('showroom_id', $showroomId)
                                ->where('accepted', 0)
                                ->update(['accepted' => 1]);

                            /** 2) Получаем дневной баланс */
                            $dailyBalance = CashDailyBalance::query()
                                ->whereDate('date', $date)
                                ->where('showroom_id', $showroomId)
                                ->first();

                            /** 3) Если баланс был изменён вручную — проверяем операции после этого */
                            if ($dailyBalance?->manually_changed) {

                                $hasOperationsAfterManual = Expense::query()
                                    ->whereDate('date', $date)
                                    ->where('showroom_id', $showroomId)
                                    ->where(function ($q) use ($dailyBalance) {
                                        $q->where('created_at', '>', $dailyBalance->updated_at)
                                            ->orWhere('updated_at', '>', $dailyBalance->updated_at);
                                    })
                                    ->exists();

                                if (! $hasOperationsAfterManual) {
                                    return; // ручной баланс актуален
                                }

                                $dailyBalance->update(['manually_changed' => 0]);
                            }

                            /** 4) Все операции дня */
                            $operations = Expense::query()
                                ->whereDate('date', $date)
                                ->where('showroom_id', $showroomId)
                                ->orderBy('created_at')
                                ->get();

                            if ($operations->isEmpty()) {
                                return;
                            }

                            /** 5) Opening balance = closing предыдущего дня */
                            $openingBalance = CashDailyBalance::query()
                                ->where('showroom_id', $showroomId)
                                ->whereDate('date', '<', $date)
                                ->orderBy('date', 'desc')
                                ->value('closing_balance') ?? 0;

                            /** 6) Считаем closing_balance */
                            $totalIncome = $operations->where('income_type', '!=', 2)->sum('income');
                            $totalExpense = $operations->where('income_type', '!=', 2)->sum('expense');

                            $closingBalance = $openingBalance + $totalIncome - $totalExpense;

                            /** 7) Обновляем дневной баланс */
                            CashDailyBalance::updateOrCreate(
                                ['date' => $date, 'showroom_id' => $showroomId],
                                [
                                    'opening_balance' => $openingBalance,
                                    'closing_balance' => $closingBalance,
                                    'approved' => true,
                                    'manually_changed' => 0,
                                ]
                            );

                            /** 8) Пересчитываем remaining_cash */
                            $currentBalance = $openingBalance;

                            foreach ($operations as $op) {
                                if ($op->income_type !== 2) {
                                    $currentBalance += $op->income - $op->expense;
                                }

                                $op->update(['remaining_cash' => $currentBalance]);
                            }
                        });
                    }),
                Action::make('accept')

                    ->label(fn($record) => $record->accepted ? 'Принято' : 'Принять')
                    ->icon('heroicon-o-check-circle')
                    ->button()
                    ->size('xs')
                    ->color(fn($record) => $record->accepted ? 'success' : 'danger')
                    ->visible(false)
                    //->visible(fn() => Auth::user()?->role === 'admin')
                    ->disabled(fn($record) => $record->accepted)
                    ->requiresConfirmation()
                    ->action(function ($record) {

                        /** 1️⃣ Помечаем операцию как принятую */
                        $record->update(['accepted' => 1]);

                        $date = $record->date;
                        $showroomId = $record->showroom_id;

                        /** 2️⃣ Получаем дневной баланс */
                        $dailyBalance = CashDailyBalance::query()
                            ->whereDate('date', $date)
                            ->where('showroom_id', $showroomId)
                            ->first();

                        /** 3️⃣ Если баланс был изменён вручную — проверяем, были ли операции после этого */
                        if ($dailyBalance?->manually_changed) {

                            $hasOperationsAfterManual = Expense::query()
                                ->whereDate('date', $date)
                                ->where('showroom_id', $showroomId)
                                ->where(function ($q) use ($dailyBalance) {
                                    $q->where('created_at', '>', $dailyBalance->updated_at)
                                        ->orWhere('updated_at', '>', $dailyBalance->updated_at);
                                })
                                ->exists();

                            // ⛔ Ничего не делаем — ручной баланс актуален
                            if (!$hasOperationsAfterManual) {
                                return;
                            }

                            // ❗ Снимаем ручной режим
                            $dailyBalance->update(['manually_changed' => 0]);
                        }

                        /** 4️⃣ Все операции дня (включая только что принятую) */
                        $operations = Expense::query()
                            ->whereDate('date', $date)
                            ->where('showroom_id', $showroomId)
                            ->orderBy('created_at')
                            ->get();

                        if ($operations->isEmpty()) {
                            return;
                        }

                        /** 5️⃣ Opening balance = closing предыдущего дня */
                        $openingBalance = CashDailyBalance::query()
                            ->where('showroom_id', $showroomId)
                            ->whereDate('date', '<', $date)
                            ->orderBy('date', 'desc')
                            ->value('closing_balance') ?? 0;

                        /** 6️⃣ Считаем closing_balance */
                        $totalIncome = $operations
                            ->where('income_type', '!=', 2)
                            ->sum('income');

                        $totalExpense = $operations
                            ->where('income_type', '!=', 2)
                            ->sum('expense');

                        $closingBalance = $openingBalance + $totalIncome - $totalExpense;

                        /** 7️⃣ Обновляем дневной баланс */
                        CashDailyBalance::updateOrCreate(
                            [
                                'date' => $date,
                                'showroom_id' => $showroomId,
                            ],
                            [
                                'opening_balance' => $openingBalance,
                                'closing_balance' => $closingBalance,
                                'approved' => true,
                                'manually_changed' => 0,
                            ]
                        );

                        /** 8️⃣ Пересчитываем remaining_cash */
                        $currentBalance = $openingBalance;

                        foreach ($operations as $op) {
                            if ($op->income_type !== 2) {
                                $currentBalance += $op->income - $op->expense;
                            }

                            $op->update([
                                'remaining_cash' => $currentBalance,
                            ]);
                        }
                    }),

                EditAction::make('edit')
                    ->label('Изменить')
                    ->icon('heroicon-o-pencil-square')
                    ->button()
                    ->size('xs')
                    ->color('success')
                    //->visible(fn() => auth()->user()?->role === 'admin')
                    ->slideOver()
                    ->modalHeading('Редактирование'),
                DeleteAction::make('delete')
                    ->label('Удалить')
                    ->icon('heroicon-o-trash')
                    ->button()
                    //->visible(fn() => (auth()->user()?->role === 'admin' ||  auth()->user()?->role === 'manager'))
                    ->size('xs')
                    ->color('danger')


            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->button()->size('xs')->visible(fn() => auth()->user()?->role === 'admin'),
                ]),
            ])
            ->recordAction('edit')   // откроет EditAction при клике по строке
            ->recordClasses('cursor-pointer')
            ->columnManager(false)
            ->striped()->defaultPaginationPageOption(25);
    }


}
