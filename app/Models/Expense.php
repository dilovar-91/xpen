<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'accepted',
        'tags',
        'comment',
    ];

    protected $casts = [
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

            // 💰 Дельта (всегда одинаковая)
            $delta = ($record->income ?? 0) - ($record->expense ?? 0);

            /**
             * =========================
             * 1️⃣ BALANCE (ВСЕГДА)
             * =========================
             */
            $lastBalanceRecord = self::where('showroom_id', $record->showroom_id)
                ->whereDate('date', '<=', $record->date)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->first();

            $lastBalance = $lastBalanceRecord?->balance ?? 0;

            // баланс всегда суммируется
            $record->balance = $lastBalance + $delta;

            /**
             * =========================
             * 2️⃣ REMAINING CASH
             * =========================
             */
            $lastCash = $lastBalanceRecord?->remaining_cash ?? 0;

            if ((int) $record->income_type === 1) {
                // влияет на кассу
                $record->remaining_cash = $lastCash + $delta;
            } else {
                // не влияет на кассу
                $record->remaining_cash = $lastCash;
            }
        });
    }


}
