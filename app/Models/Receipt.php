<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'showroom_id',
        'full_name',
        'phone',
        'car_mark',
        'car_model',
        'vin_number',
        'comment',
        'full_price',
        'repayment_date',
        'type_id',
        'closed',
        'approved',
        'closed_date',
    ];

    protected $casts = [
        'full_price' => 'decimal:2',
        'repayment_date' => 'date',
        'closed' => 'boolean',
        'closed_date' => 'date',
        'type_id' => 'integer',
        'approved' => 'boolean',
    ];

    // Связь с салоном
    public function showroom()
    {
        return $this->belongsTo(Showroom::class);
    }


    public function items()
    {
        return $this->hasMany(ReceiptItem::class);
    }
}

