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
        'expense',
        'balance',
        'comment',
    ];
    public function showroom()
    {
        return $this->belongsTo(Showroom::class);
    }
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
