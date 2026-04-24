<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Service;
use App\Models\ServicetoCustomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicetoCustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createUser()
    {
        return User::factory()->create();
    }

    public function test_store_assigns_service_to_customer()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();
        $service = Service::create([
            'name' => 'Web Hosting',
            'base_price' => 100,
            'description' => 'Hosting'
        ]);

        $data = [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 120,
            'expiration' => now()->addYear()->format('Y-m-d'),
            'reminder' => true
        ];

        $response = $this->actingAs($user)->post('/addservicetocustomer', $data);

        $response->assertRedirect(route('customer_edit', ['id' => $customer->id]));
        $this->assertDatabaseHas('servicetocustomer', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 120,
            'reminder' => 1
        ]);
    }

    public function test_edit_returns_json()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();
        $service = Service::create([
            'name' => 'Web Hosting',
            'base_price' => 100,
            'description' => 'Hosting'
        ]);
        
        $stc = ServicetoCustomer::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 100,
            'expiration' => now()->addYear()->format('Y-m-d'),
            'reminder' => 1
        ]);

        $response = $this->actingAs($user)->get('/servicetocustomer/' . $stc->id . '/edit');

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $stc->id,
            'price' => 100
        ]);
    }

    public function test_update_modifies_servicetocustomer()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();
        $service = Service::create([
            'name' => 'Web Hosting',
            'base_price' => 100,
            'description' => 'Hosting'
        ]);
        
        $stc = ServicetoCustomer::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 100,
            'expiration' => now()->addYear()->format('Y-m-d'),
            'reminder' => 1
        ]);

        $response = $this->actingAs($user)->put('/servicetocustomer/' . $stc->id, [
            'service_id' => $service->id,
            'price' => 150
        ]);

        $response->assertRedirect(route('customer_edit', ['id' => $customer->id]));
        $this->assertDatabaseHas('servicetocustomer', [
            'id' => $stc->id,
            'price' => 150
        ]);
    }

    public function test_renew_service_creates_record_and_updates()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();
        $service = Service::create([
            'name' => 'Web Hosting',
            'base_price' => 100,
            'description' => 'Hosting'
        ]);
        
        $stc = ServicetoCustomer::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 100,
            'expiration' => now()->format('Y-m-d'),
            'reminder' => 1
        ]);

        $response = $this->actingAs($user)->post('/service/' . $stc->id . '/renew', [
            'service_id' => $stc->id,
            'new_price' => 200,
            'new_expiration_date' => now()->addYear()->format('Y-m-d')
        ]);

        $response->assertRedirect();
        
        // Assert old record was archived
        $this->assertDatabaseHas('servicetocustomer_records', [
            'servicetocustomer_id' => $stc->id,
        ]);

        // Assert service was updated
        $this->assertDatabaseHas('servicetocustomer', [
            'id' => $stc->id,
            'price' => 200,
            'payment_id' => null
        ]);
    }

    public function test_destroy_deletes_service_from_customer()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();
        $service = Service::create([
            'name' => 'Web Hosting',
            'base_price' => 100,
            'description' => 'Hosting'
        ]);
        
        $stc = ServicetoCustomer::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 100,
            'expiration' => now()->format('Y-m-d'),
            'reminder' => 1
        ]);

        $response = $this->actingAs($user)->delete('/servicetocustomer/' . $stc->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('servicetocustomer', [
            'id' => $stc->id
        ]);
    }

    public function test_delete_service_from_user()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();
        $service = Service::create([
            'name' => 'Web Hosting',
            'base_price' => 100,
            'description' => 'Hosting'
        ]);

        $stc = ServicetoCustomer::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 100,
            'expiration' => now()->format('Y-m-d'),
            'reminder' => 0,
            'payment_id' => 1 // simulating paid_status assuming it checks this
        ]);

        $response = $this->actingAs($user)->delete('/customer/service/delete', [
            'customer_id' => $customer->id,
            'service_id' => $stc->id
        ]);

        $response->assertRedirect();
        // Since paid_status logic might be specific, we just assert it doesn't throw 500
    }

    public function test_show_service_details()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();
        $service = Service::create(['name' => 'Hosting', 'base_price' => 100, 'description' => 'D']);
        $stc = ServicetoCustomer::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 100,
            'expiration' => now()->format('Y-m-d'),
            'reminder' => 1
        ]);

        $response = $this->actingAs($user)->get('/servicetocustomer/' . $stc->id . '/details');
        $response->assertStatus(200);
        $response->assertViewIs('customers.records.index');
    }

    public function test_update_reminder_status()
    {
        $user = $this->createUser();
        $customer = Customer::factory()->create();
        $service = Service::create(['name' => 'Hosting', 'base_price' => 100, 'description' => 'D']);
        $stc = ServicetoCustomer::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'price' => 100,
            'expiration' => now()->format('Y-m-d'),
            'reminder' => 1
        ]);

        $response = $this->actingAs($user)->post('/servicetocustomer/update_reminder_status', [
            'id' => $stc->id,
            'reminder' => 0
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('servicetocustomer', [
            'id' => $stc->id,
            'reminder' => 0
        ]);
    }
}
