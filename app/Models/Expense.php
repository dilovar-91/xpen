<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Expense extends Model
{


    use HasFactory;

    protected $fillable = [
        'manager_id',
        'type_id',
        'tag_id',
        'showroom_id',
        'date',
        'income',
        'income_type',
        'expense',
        'balance',
        'remaining_cash',
        'online_cash',
        'accepted',
        'tags',
        'comment',
    ];

    protected $casts = [
        'date' => 'date',
        'tags' => 'array',
    ];
    public function showroom()
    {
        return $this->belongsTo(Showroom::class);
    }
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }


    protected static function booted()
    {
        static::creating(function (Expense $record) {

            if ($record->income_type === null) {
                $record->income_type = 1;
            }

            $previousRemaining = self::getPreviousRemainingCash($record);

            // Онлайн-приход — не влияет на кассу
            if ((int) $record->type_id === 1 && (int) $record->income_type === 2) {
                $record->online_cash = $record->income;
                $record->remaining_cash = $previousRemaining;
                return;
            }

            $record->remaining_cash = $previousRemaining
                + ($record->income ?? 0)
                - ($record->expense ?? 0);
        });

        static::created(function (Expense $record) {
            // ✅ после добавления строки пересчитываем остаток дня
            self::recalculateDay($record->date, $record->showroom_id);
        });

        static::updating(function (Expense $record) {
            if ($record->isDirty(['income', 'expense', 'type_id', 'income_type', 'showroom_id'])) {
                $record->accepted = 1;
            }
        });

        static::updated(function (Expense $record) {
            // если менялись деньги/тип — пересчитать день
            if ($record->wasChanged(['income', 'expense', 'type_id', 'income_type', 'showroom_id', 'date'])) {
                self::recalculateDay($record->date, $record->showroom_id);
            }
        });

        static::deleted(function (Expense $record) {
            self::recalculateDay($record->date, $record->showroom_id);
        });
    }

    // === Публичный метод вместо Action-логики ===
    public function acceptAndRecalculate(): void
    {
        DB::transaction(function () {
            $this->update(['accepted' => 1]);
            self::recalculateDay($this->date, $this->showroom_id);
        });
    }

    // === Основной пересчёт дня (CashDailyBalance + remaining_cash) ===
    public static function recalculateDay($date, int $showroomId): void
    {
        DB::transaction(function () use ($date, $showroomId) {

            $dailyBalance = CashDailyBalance::query()
                ->whereDate('date', $date)
                ->where('showroom_id', $showroomId)
                ->first();

            // Если баланс был изменён вручную и после этого операций не было — не трогаем
            if ($dailyBalance?->manually_changed) {
                if (! self::hasOperationsAfter($date, $showroomId, $dailyBalance->updated_at)) {
                    return;
                }
                // иначе снимаем ручной режим и пересчитываем
                $dailyBalance->update(['manually_changed' => 0]);
            }

            $operations = self::query()
                ->whereDate('date', $date)
                ->where('showroom_id', $showroomId)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            if ($operations->isEmpty()) {
                return;
            }

            $openingBalance = CashDailyBalance::query()
                ->where('showroom_id', $showroomId)
                ->whereDate('date', '<', $date)
                ->orderBy('date', 'desc')
                ->value('closing_balance') ?? 0;

            $totalIncome  = $operations->where('income_type', '!=', 2)->sum('income');
            $totalExpense = $operations->where('income_type', '!=', 2)->sum('expense');

            $closingBalance = $openingBalance + $totalIncome - $totalExpense;

            CashDailyBalance::updateOrCreate(
                ['date' => $date, 'showroom_id' => $showroomId],
                [
                    'opening_balance' => $openingBalance,
                    'closing_balance' => $closingBalance,
                    'approved' => true,
                    'manually_changed' => 0,
                ]
            );

            // remaining_cash по операциям дня
            $currentBalance = $openingBalance;

            foreach ($operations as $op) {
                if ((int) $op->income_type !== 2) {
                    $currentBalance += ($op->income ?? 0) - ($op->expense ?? 0);
                }

                // updateQuietly чтобы лишний раз не дергать события
                $op->updateQuietly(['remaining_cash' => $currentBalance]);
            }
        });
    }

    // === База для remaining_cash при создании записи ===
    private static function getPreviousRemainingCash(Expense $record): float
    {
        // последняя операция до текущей
        $previousExpense = self::query()
            ->where('showroom_id', $record->showroom_id)
            ->where(function ($q) use ($record) {
                $q->whereDate('date', $record->date)
                    ->orWhereDate('date', '<', $record->date);
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $previousRemaining = (float) ($previousExpense?->remaining_cash ?? 0);

        // дневной баланс ДО текущей даты
        $prevDailyBalance = CashDailyBalance::query()
            ->where('showroom_id', $record->showroom_id)
            ->whereDate('date', '<', $record->date)
            ->orderBy('date', 'desc')
            ->first();

        if ($prevDailyBalance?->manually_changed) {
            if (! self::hasOperationsAfter($prevDailyBalance->date, $record->showroom_id, $prevDailyBalance->updated_at)) {
                return (float) $prevDailyBalance->closing_balance;
            }
        }

        return $previousRemaining;
    }

    private static function hasOperationsAfter($date, int $showroomId, $timestamp): bool
    {
        return self::query()
            ->whereDate('date', $date)
            ->where('showroom_id', $showroomId)
            ->where(function ($q) use ($timestamp) {
                $q->where('created_at', '>', $timestamp)
                    ->orWhere('updated_at', '>', $timestamp);
            })
            ->exists();
    }


    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }



}
