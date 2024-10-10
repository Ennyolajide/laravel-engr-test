<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Batch;
use App\Models\Order;
use Faker\Factory as Faker;
use App\Mail\OrderSubmitted;
use App\Services\OrderService;
use Tests\Traits\OrdersTestHelpers;
use Illuminate\Support\Facades\Mail;

use Illuminate\Foundation\Testing\RefreshDatabase;



class SubmitOrderApiTest extends TestCase
{
    use RefreshDatabase, OrdersTestHelpers;
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
    public function it_assigns_order_to_batch_after_creation()
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

        // call to a private method for testing purposes | getBatchKey
        $batchKey = $this->callPrivateMethod( new OrderService(), 'getBatchKey', [$order]);

        // Assert that the batch key is not empty
        $this->assertNotEmpty($batchKey);

        // Assert that the batch has been created
        $this->assertDatabaseHas('batches', ['batch_key' => $batchKey]);

        // Assert that the order has been assigned to the batch
        $this->assertEquals($order->batch_id, Batch::where('batch_key', $batchKey)->first()->id);
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

}
