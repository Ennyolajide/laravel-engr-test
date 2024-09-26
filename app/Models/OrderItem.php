<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{

    protected $fillable = ['order_id', 'name', 'unit_price', 'quantity', 'total']; // Allow mass assignment for these fields

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class); // Define the relationship with the Order model
    }

    public function calculateTotal(): float
    {
        return $this->unit_price * $this->quantity; // Calculate the total for this order item
    }
}
