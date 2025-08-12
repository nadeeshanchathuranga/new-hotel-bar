<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerItem extends Model
{
       use HasFactory;

    protected $fillable = ['owner_id', 'discount_value', 'current_discount', 'status'];


    public function owner() {
        return $this->belongsTo(Owner::class);
    }


    protected $casts = [
    'month' => 'date:Y-m', // will return a Carbon and serialize as 'YYYY-MM'
];

}
