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
        'comment',
        'full_price',
        'part_price',
        'repayment_date',
        'type_id',
        'group_id',
        'closed',
    ];

    protected $casts = [
        'full_price' => 'decimal:2',
        'repayment_date' => 'date',
        'closed' => 'datetime',
        'type_id' => 'integer',
    ];

    // Связь с салоном
    public function showroom()
    {
        return $this->belongsTo(Showroom::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'group_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}

