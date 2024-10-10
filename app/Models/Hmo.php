<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hmo extends Model
{
    use HasFactory;


    protected $fillable = ['name', 'code', 'email', 'batching_criteria']; // Allow mass assignment for these fields

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class); // Define the one-to-many relationship with orders
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
}
