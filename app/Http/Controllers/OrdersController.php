<?php

namespace App\Http\Controllers;

use App\Models\Hmo;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Mail\OrderSubmitted;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log; // To log the error
use Illuminate\Validation\ValidationException;

class OrdersController extends Controller
{
    public function store(Request $request)
    {
        // Validate incoming request
        try {
            $request->validate([
                'hmo_code' => 'required|string|max:255|exists:hmos,code', // Check if hmo_code exists in hmos table
                'provider' => 'required|string|max:255',
                'encounter_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.name' => 'required|string|max:255',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.quantity' => 'required|integer|min:1',
            ]);
        } catch (ValidationException $e) {
            // Return custom error response for validation failures
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            // Find the HMO and get its email
            $hmo = Hmo::where('code', $request->hmo_code)->firstOrFail();

            // Create the order
            $order = Order::create($request->only(['hmo_code', 'provider', 'encounter_date']));

            // Create order items
            $order->items()->createMany(
                collect($request->items)
                    ->map(function ($item) {
                        return [
                            'name' => $item['name'],
                            'unit_price' => $item['unit_price'],
                            'quantity' => $item['quantity'],
                            'total' => $item['unit_price'] * $item['quantity'],
                        ];
                    })
                    ->toArray() // Convert the collection back to an array
            );

            // Send email notification to HMO

            Mail::to($hmo->email)->send(new OrderSubmitted($order));

            return response()->json(['message' => 'Order submitted successfully.'], 201);

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Order submission failed: ' . $e->getMessage());

            return response()->json(['error' => 'Order submission failed: ' . $e->getMessage()], 500);
        }
    }
}
