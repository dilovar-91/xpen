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
            if ($record->type_id === 1 &&  (int) $record->income_type === 2) {
                $record->online_cash = $record->income;

            }


        });


        static::updating(function ($record) {
            if ($record->isDirty(['income', 'expense', 'type_id', 'income_type', 'showroom_id'])) {
                $record->accepted = 0;
            }
        });
    }


}
