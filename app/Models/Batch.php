<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = ['batch_key', 'hmo_id'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
