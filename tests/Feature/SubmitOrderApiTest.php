<?php

namespace Tests\Feature;

use App\Models\Hmo;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Mail\OrderSubmitted;
use Faker\Factory as Faker;

class SubmitOrderApiTest extends TestCase
{
    use RefreshDatabase;
    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake(); // Fake the email sending
        $this->faker = Faker::create();
    }

    /** @test */
    /** @test */
    public function it_creates_an_order()
    {
        $orderData = $this->createOrderData();

        // Send the request and assert response status
        $response = $this->postJson('api/orders/create', $orderData);
        $response->assertStatus(201);

        // Retrieve the created order from the database
        $order = Order::where('hmo_code', $orderData['hmo_code'])
            ->where('provider', $orderData['provider'])
            ->where('encounter_date', $orderData['encounter_date'])
            ->first();

        // Assert the order exists in the database
        $this->assertNotNull($order);

        // Assert that the order items have been created and match
        $this->assertDatabaseHas('orders', [
            'hmo_code' => $orderData['hmo_code'],
            'provider' => $orderData['provider'],
            'encounter_date' => $orderData['encounter_date'],
        ]);

        foreach ($orderData['items'] as $item) {
            $this->assertDatabaseHas('order_items', [
                'order_id' => $order->id,
                'name' => $item['name'],
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'total' => $item['unit_price'] * $item['quantity'], // Check total
            ]);
        }
    }


    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->postJson('api/orders/create', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items', 'hmo_code', 'provider', 'encounter_date']);
    }

    /** @test */
    public function it_validates_item_fields()
    {
        $orderData = $this->createOrderData(); // Generate valid data

        // Directly assign invalid values to the fields
        $orderData['items'][0]['quantity'] = -1;
        $orderData['items'][0]['unit_price'] = 'invalid_price';

        // Send the request with the invalid data
        $response = $this->postJson('api/orders/create', $orderData);

        // Assert validation errors for specific fields
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'items.0.unit_price',
            'items.0.quantity',
        ]);

        // Assert no orders or items were created
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }


    /** @test */
    public function it_sends_notification_to_hmo()
    {
        $data = $this->createOrderData(); // Create order data
        // Send the POST request to create the order
        $response = $this->postJson('api/orders/create', $data);
        // Ensure the response status is 201 (created)
        $response->assertStatus(201);
        //Assert that an email was sent to the HMO email
        Mail::assertSent(OrderSubmitted::class);
    }

    protected function createOrderData()
    {
        // Prepare the order data
        return [
            'encounter_date' => $this->faker->date, // Generate a random date
            'items' => $this->buildOrderItems()->toArray(), // Generate order items
            'hmo_code' => Hmo::factory()->create()->code, // Use the HMO code created above
            'provider' => 'Provide-'.$this->faker->lexify('?'), // Use the provider ID created above
        ];
    }

    protected function buildOrderItems()
    {
        return collect(range(1, 5))->map(function () {
            return [
                'name' => $this->faker->word,
                'quantity' => $this->faker->numberBetween(1, 10),
                'unit_price' => $this->faker->randomFloat(2, 1, 100)
            ];
        });
    }
}
