<?php

namespace App\Http\Controllers;

use App\Models\Hmo;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Mail\OrderSubmitted;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Validation\ValidationException;

class OrdersController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function store(StoreOrderRequest $request)
    {
        DB::beginTransaction(); // Begin a database transaction

        try {
            // Find the HMO and get its email
            $hmo = Hmo::where('code', $request->hmo_code)->firstOrFail();

            // Create the order
            $order = Order::create($request->only(['hmo_code', 'provider', 'encounter_date']));

            // Create order items
            $this->createOrderItems($order, $request->items);

            // Add Order to Batch
            $this->orderService->addOrderToBatch($order);

            DB::commit(); // Commit the transaction

            // Send email notification to HMO
            Mail::to($hmo->email)->send(new OrderSubmitted($order));

            return response()->json(['message' => 'Order submitted successfully.'], 201);

        } catch (\Exception $e) {

            DB::rollBack(); // Rollback the transaction
            // Log the error for debugging
            Log::error('Order submission failed: ' . $e->getMessage());

            return response()->json(['error' => 'Order submission failed: ' . $e->getMessage()], 500);
        }
    }

    protected function createOrderItems(Order $order, array $items)
    {
        $order->items()->createMany(
            collect($items)->map(function ($item) {
                return [
                    'name' => $item['name'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['unit_price'] * $item['quantity'],
                ];
            })->toArray()
        );
    }
}
