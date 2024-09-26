<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['hmo_code', 'encounter_date', 'provider'];


    public function hmo()
    {
        return $this->belongsTo(Hmo::class, 'hmo_code', 'code');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
