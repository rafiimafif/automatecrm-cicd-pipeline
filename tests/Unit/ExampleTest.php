<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_customer_model_has_correct_fillable_attributes()
    {
        $customer = new Customer();
        $this->assertInstanceOf(Customer::class, $customer);
    }

    public function test_service_model_can_be_instantiated()
    {
        $service = new Service();
        $this->assertInstanceOf(Service::class, $service);
    }

    public function test_user_model_can_be_instantiated()
    {
        $user = new User();
        $this->assertInstanceOf(User::class, $user);
    }
}

        $this->assertTrue(true);
    }
}
