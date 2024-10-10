<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Hmo;
use App\Models\Order;
use App\Services\OrderService;
use Tests\Traits\OrdersTestHelpers;
use Illuminate\Foundation\Testing\RefreshDatabase;


class OrderServiceTest extends TestCase
{
    use RefreshDatabase, OrdersTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_returns_batch_key_based_on_encounter_date()
    {
        // Create an order | month_of_encounter is the default batching criteria
        $order = Order::create($this->createOrderData());
        // Get the batch key for the order using a private method
        $batchKey = $this->callPrivateMethod(new OrderService(), 'getBatchKey', [$order]);
        // Parse the order's encounter date
        $orderDate = Carbon::parse($order->encounter_date);

        $expectedBatchKey = "{$order->provider} {$orderDate->format('M Y')}";
        // Assert the expected batch key contains the Provider's name, month, and year
        $this->assertEquals($expectedBatchKey, $batchKey);
    }

    /** @test */
    public function it_returns_batch_key_based_on_submission_date()
    {
        // Create HMO A with special batching criteria
        $hmo = Hmo::factory()->create(['batching_criteria' => 'day_of_submission']);

        $order = Order::create($this->createOrderData($hmo));

        // Call the private method to get the batch key using reflection
        $batchKey = $this->callPrivateMethod(new OrderService(), 'getBatchKey', [$order]);

        // Parse the order's submission date
        $submissionDate = Carbon::parse($order->created_at);

        // Construct the expected batch key
        $expectedBatchKey = "{$order->provider} {$submissionDate->format('M Y')}"; // Use full month name

        // Assert the expected batch key
        $this->assertEquals($expectedBatchKey, $batchKey);
    }

}
