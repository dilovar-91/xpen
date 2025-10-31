<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Showroom extends Model
{
    protected $fillable = ['name', 'sort'];


    protected $casts = [
        'sort' => 'integer',
    ];

}
