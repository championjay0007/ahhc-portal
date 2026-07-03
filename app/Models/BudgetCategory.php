<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetCategory extends Model
{
    protected $fillable = ['name', 'description'];

    public function transactions()
    {
        return $this->hasMany(BudgetTransaction::class, 'category_id');
    }
}
