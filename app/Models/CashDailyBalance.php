<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashDailyBalance extends Model
{
    use HasFactory;

    protected $table = 'cash_daily_balances';

    // Какие поля можно массово заполнять
    protected $fillable = [
        'date',
        'showroom_id',
        'opening_balance',
        'closing_balance',
        'approved',
        'manually_changed'
    ];

    // Типы данных
    protected $casts = [
        'date' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'approved' => 'boolean',
        'manually_changed' => 'boolean',
    ];

    /**
     * Связь с кассой/шоурумом
     */
    public function showroom()
    {
        return $this->belongsTo(Showroom::class);
    }

    /**
     * Scope для одобрённых записей
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    /**
     * Scope для конкретной даты
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope для конкретного шоурума
     */
    public function scopeForShowroom($query, $showroomId)
    {
        return $query->where('showroom_id', $showroomId);
    }
}
