<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        static::creating(function ($record) {

            if ($record->income_type === null) {
                $record->income_type = 1;
            }

            // ✅ 1) Найдём последний дневной баланс ДО текущей даты
            $prevDailyBalance = CashDailyBalance::query()
                ->where('showroom_id', $record->showroom_id)
                ->whereDate('date', '<', $record->date)
                ->orderBy('date', 'desc')
                ->first();

            // ✅ 2) Найдём последнюю операцию ДО текущей (старая логика)
            $previousExpense = self::query()
                ->where('showroom_id', $record->showroom_id)
                ->where(function ($q) use ($record) {
                    $q->whereDate('date', $record->date)
                        ->orWhereDate('date', '<', $record->date);
                })
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $previousRemaining = $previousExpense?->remaining_cash ?? 0;

            // ✅ 3) Если дневной баланс был изменён вручную —
            // и после этого НЕ было новых операций за тот день,
            // то в качестве базы используем closing_balance
            if ($prevDailyBalance?->manually_changed) {

                $hasOperationsAfterManualForThatDay = Expense::query()
                    ->whereDate('date', $prevDailyBalance->date)
                    ->where('showroom_id', $record->showroom_id)
                    ->where(function ($q) use ($prevDailyBalance) {
                        $q->where('created_at', '>', $prevDailyBalance->updated_at)
                            ->orWhere('updated_at', '>', $prevDailyBalance->updated_at);
                    })
                    ->exists();

                // ✅ Если после ручного изменения ничего не было — берем closing_balance
                if (! $hasOperationsAfterManualForThatDay) {
                    $previousRemaining = (float) $prevDailyBalance->closing_balance;
                }
            }

            // ✅ 4) Онлайн-приход — не влияет на кассу
            if (
                (int) $record->type_id === 1 &&
                (int) $record->income_type === 2
            ) {
                $record->online_cash = $record->income;
                $record->remaining_cash = $previousRemaining;
                return;
            }

            // ✅ 5) Обычный расчёт кассы от выбранной базы
            $record->remaining_cash =
                $previousRemaining
                + ($record->income ?? 0)
                - ($record->expense ?? 0);
        });

        static::updating(function ($record) {
            if ($record->isDirty(['income', 'expense', 'type_id', 'income_type', 'showroom_id'])) {
                $record->accepted = 0;
            }
        });
    }


    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }



}
