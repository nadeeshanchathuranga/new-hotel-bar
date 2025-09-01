<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 

class ServiceCharge extends Model
{
    use HasFactory;
    protected $table = 'service_charges';
    protected $fillable = ['service_charge','service_check'];
}
