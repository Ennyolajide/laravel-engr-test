<?php

namespace Tests\Traits;

use App\Models\Hmo;
use ReflectionClass;

trait OrdersTestHelpers
{
    /**
     * Call a private or protected method on a given object.
     *
     * @param object $object The object containing the method
     * @param string $methodName The name of the private/protected method
     * @param array $parameters The parameters to pass to the method
     * @return mixed The result of the invoked method
     */
    public function callPrivateMethod($object, string $methodName, array $parameters = [])
    {
        // Create a reflection class instance
        $reflection = new ReflectionClass($object);
        // Get the method from the reflection class
        $method = $reflection->getMethod($methodName);
        // Set the method accessible
        $method->setAccessible(true);
        // Invoke the method with the given parameters
        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Create test data for orders.
     *
     * @return array
     */
    protected function createOrderData(Hmo $hmo = null)
    {
        $faker = \Faker\Factory::create();

        return [
            'encounter_date' => $faker->date(),
            'items' => $this->buildOrderItems(),
            'hmo_code' => optional($hmo)->code ?? Hmo::factory()->create()->code,
            'provider' => 'Provider-' . $faker->lexify('????')
        ];
    }

    /**
     * Build order items data.
     *
     * @return array
     */
    protected function buildOrderItems()
    {
        $faker = \Faker\Factory::create();

        return collect(range(1, 5))->map(function () use ($faker) {
            return [
                'name' => $faker->word(),
                'quantity' => $faker->numberBetween(1, 10),
                'unit_price' => $faker->randomFloat(2, 1, 100),
            ];
        })->toArray();
    }
}
