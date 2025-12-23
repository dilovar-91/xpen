<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptItem extends Model
{

    protected $fillable = [
        'receipt_id',
        'amount',
        'date',
        'comment',
    ];
    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];
}
