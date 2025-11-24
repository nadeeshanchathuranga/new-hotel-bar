<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'status'];

    public function items() {
        return $this->hasMany(OwnerItem::class);
    }

    // public function currentItem() {
    //     return $this->hasOne(OwnerItem::class)->where('current_discount', true);
    // }

  public function thisMonthItem()
{
    $month = now('Asia/Colombo')->format('Y-m');
    return $this->hasOne(OwnerItem::class, 'owner_id')->where('month', $month);
}

public function latestItem()
{
    return $this->hasOne(OwnerItem::class, 'owner_id')->latest('month');
}


}
