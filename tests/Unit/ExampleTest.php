<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServicetoCustomer;
use App\Models\User;
use App\Services\RenewalService;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_customer_model_can_be_instantiated()
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

    public function test_payment_model_can_be_instantiated()
    {
        $payment = new Payment();
        $this->assertInstanceOf(Payment::class, $payment);
    }

    public function test_activity_log_model_can_be_instantiated()
    {
        $log = new ActivityLog();
        $this->assertInstanceOf(ActivityLog::class, $log);
    }

    public function test_service_to_customer_model_can_be_instantiated()
    {
        $stc = new ServicetoCustomer();
        $this->assertInstanceOf(ServicetoCustomer::class, $stc);
    }

    public function test_activity_log_fillable_fields()
    {
        $log = new ActivityLog();
        $this->assertContains('action', $log->getFillable());
        $this->assertContains('user_id', $log->getFillable());
    }

    public function test_payment_fillable_fields()
    {
        $payment = new Payment();
        $this->assertContains('price', $payment->getFillable());
        $this->assertContains('payment_type', $payment->getFillable());
    }

    public function test_service_fillable_fields()
    {
        $service = new Service();
        $this->assertContains('name', $service->getFillable());
        $this->assertContains('description', $service->getFillable());
    }

    public function test_renewal_service_can_be_instantiated()
    {
        $renewal = new RenewalService();
        $this->assertInstanceOf(RenewalService::class, $renewal);
    }

    public function test_renewal_service_handle_renewal()
    {
        $renewal = new RenewalService();
        // handleRenewal should execute without error
        $result = $renewal->handleRenewal(null);
        $this->assertNull($result);
    }

    public function test_services_count_each_service_returns_integer()
    {
        $count = \App\Http\Controllers\ServicesController::count_each_service(999);
        $this->assertIsInt($count);
    }

    public function test_home_finance_returns_array()
    {
        $finance = \App\Http\Controllers\HomeController::finance();
        $this->assertIsArray($finance);
        $this->assertCount(3, $finance);
    }
}
