<?php

namespace Tests\Unit;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_relationships_and_accessors()
    {
        $customer = Customer::factory()->create([
            'fname' => 'John',
            'lname' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $customer->full_name);

        $this->assertInstanceOf(HasMany::class, $customer->services());
        $this->assertInstanceOf(HasMany::class, $customer->payments());
        $this->assertInstanceOf(HasMany::class, $customer->servicetocustomer());
        $this->assertInstanceOf(MorphToMany::class, $customer->tags());
        $this->assertInstanceOf(MorphMany::class, $customer->notes());
        $this->assertInstanceOf(HasMany::class, $customer->deals());
        $this->assertInstanceOf(MorphMany::class, $customer->tasks());
    }
}
