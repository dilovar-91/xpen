<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'type_id'];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}

