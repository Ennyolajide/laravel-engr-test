<?php

namespace App\Services;

use App\Models\Hmo;
use App\Models\Batch;
use App\Models\Order;

class OrderService
{
    public function addOrderToBatch(Order $order)
    {

        $batchKey = $this->getBatchKey($order);
        $batch = $this->getBatch($batchKey, $order);
        $order->update(['batch_id' => $batch->id]);
    }

    public function getBatchKey(Order $order): string
    {
        $criteria = $order->hmo->batching_criteria;
        // Determine the batch date based on the batching criteria
        $batchDate = \Carbon\Carbon::parse($order->created_at)
            ->when($criteria === 'month_of_encounter', function ($date) use ($order) {
                return \Carbon\Carbon::parse($order->encounter_date);
            });

        // Return the batch key in the desired format
        return "{$order->provider} {$batchDate->format('M Y')}";
    }

    private function getBatch(string $batchKey, Order $order)
    {
        // Create a new batch record or get an existing one
        return Batch::firstOrCreate([
            'batch_key' => $batchKey,
            'hmo_id' => $order->hmo->id,
        ]);
    }
}

