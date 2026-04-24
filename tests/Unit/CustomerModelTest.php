<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Service;
use App\Models\ServicetoCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_relationships_and_accessors()
    {
        $customer = Customer::factory()->create([
            'fname' => 'John',
            'lname' => 'Doe'
        ]);

        $this->assertEquals('John Doe', $customer->full_name);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $customer->services());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $customer->payments());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $customer->servicetocustomer());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $customer->tags());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $customer->notes());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $customer->deals());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $customer->tasks());
    }
}
