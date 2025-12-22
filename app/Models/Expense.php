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

            if($record->income_type === null) {
                $record->income_type = 1;
            }

            // 1️⃣ Ищем предыдущую запись
            $previous = self::query()
                ->where('showroom_id', $record->showroom_id)
                ->where(function ($q) use ($record) {
                    $q->whereDate('date', $record->date)
                        ->orWhereDate('date', '<', $record->date);
                })
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $previousRemaining = $previous?->remaining_cash ?? 0;

            // 2️⃣ Онлайн-приход — не влияет на кассу
            if (
                (int) $record->type_id === 1 &&
                (int) $record->income_type === 2
            ) {
                $record->online_cash = $record->income;
                $record->remaining_cash = $previousRemaining;
                return;
            }

            // 3️⃣ Обычный расчёт кассы
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


}
