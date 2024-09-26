<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hmo extends Model
{
    use HasFactory;


    protected $fillable = ['name', 'code', 'email']; // Allow mass assignment for these fields

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class); // Define the one-to-many relationship with orders
    }
}
