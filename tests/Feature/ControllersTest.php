<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ControllersTest extends TestCase
{
    use RefreshDatabase;

    // ───────────────────────────────────────────────
    // Helper: create a customer directly via DB
    // ───────────────────────────────────────────────
    private function createCustomer(): int
    {
        return DB::table('customers')->insertGetId([
            'fname' => 'Test',
            'lname' => 'User',
            'email' => uniqid('cust', true).'@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createService(): int
    {
        return DB::table('services')->insertGetId([
            'name' => uniqid('Svc', true),
            'description' => 'A service',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ───────────────────────────────────────────────
    // HomeController
    // ───────────────────────────────────────────────

    public function test_welcome_page_is_accessible()
    {
        $response = $this->get('/welcome');

        $response->assertStatus(200);
    }

    // ───────────────────────────────────────────────
    // CustomersController
    // ───────────────────────────────────────────────

    public function test_authenticated_user_can_edit_customer()
    {
        $user = User::factory()->create();
        $customerId = $this->createCustomer();

        $response = $this->actingAs($user)->get('/customer_edit/'.$customerId);

        $response->assertStatus(200);
    }

    public function test_tools_page_is_accessible()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/tools');

        $response->assertStatus(200);
    }

    // ───────────────────────────────────────────────
    // ServicesController
    // ───────────────────────────────────────────────

    public function test_service_edit_returns_json()
    {
        $serviceId = $this->createService();

        $response = $this->get('/services/'.$serviceId.'/edit');

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $serviceId]);
    }

    public function test_authenticated_user_can_update_service()
    {
        $user = User::factory()->create();
        $serviceId = $this->createService();

        $response = $this->actingAs($user)->put('/services/'.$serviceId, [
            'name' => 'Updated Service',
            'description' => 'Updated description',
        ]);

        $response->assertRedirect('/services');
    }

    public function test_authenticated_user_can_delete_service_with_no_customers()
    {
        $user = User::factory()->create();
        $serviceId = $this->createService();

        $response = $this->actingAs($user)->delete('/services/'.$serviceId);

        $response->assertRedirect();
    }

    // ───────────────────────────────────────────────
    // PaymentsController
    // ───────────────────────────────────────────────

    public function test_authenticated_user_can_add_payment()
    {
        $user = User::factory()->create();
        $customerId = $this->createCustomer();

        $response = $this->actingAs($user)->post('/addpayment', [
            'customer_id' => $customerId,
            'price' => 100.00,
            'payment_date' => '2026-01-01',
            'payment_type' => 'cash',
            'notes' => 'Test payment',
        ]);

        $response->assertRedirect();
    }

    // ───────────────────────────────────────────────
    // ServicetoCustomerController
    // ───────────────────────────────────────────────

    public function test_authenticated_user_can_add_service_to_customer()
    {
        $user = User::factory()->create();
        $customerId = $this->createCustomer();
        $serviceId = $this->createService();

        $response = $this->actingAs($user)->post('/addservicetocustomer', [
            'customer_id' => $customerId,
            'service_id' => $serviceId,
            'price' => 50.00,
            'expiration' => '2027-01-01',
        ]);

        $response->assertRedirect();
    }

    public function test_servicetocustomer_edit_returns_json()
    {
        $customerId = $this->createCustomer();
        $serviceId = $this->createService();

        $stc = DB::table('servicetocustomer')->insertGetId([
            'customer_id' => $customerId,
            'service_id' => $serviceId,
            'price' => 50.00,
            'expiration' => '2027-01-01',
            'reminder' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/servicetocustomer/'.$stc.'/edit');

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $stc]);
    }

    public function test_authenticated_user_can_update_servicetocustomer()
    {
        $user = User::factory()->create();
        $customerId = $this->createCustomer();
        $serviceId = $this->createService();

        $stcId = DB::table('servicetocustomer')->insertGetId([
            'customer_id' => $customerId,
            'service_id' => $serviceId,
            'price' => 50.00,
            'expiration' => '2027-01-01',
            'reminder' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->put('/servicetocustomer/'.$stcId, [
            'service_id' => $serviceId,
            'price' => 75.00,
        ]);

        $response->assertRedirect();
    }

    public function test_authenticated_user_can_delete_servicetocustomer()
    {
        $user = User::factory()->create();
        $customerId = $this->createCustomer();
        $serviceId = $this->createService();

        $stcId = DB::table('servicetocustomer')->insertGetId([
            'customer_id' => $customerId,
            'service_id' => $serviceId,
            'price' => 50.00,
            'expiration' => '2027-01-01',
            'reminder' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->delete('/servicetocustomer/'.$stcId);

        $response->assertRedirect();
    }

    public function test_update_reminder_status_returns_json()
    {
        $user = User::factory()->create();
        $customerId = $this->createCustomer();
        $serviceId = $this->createService();

        $stcId = DB::table('servicetocustomer')->insertGetId([
            'customer_id' => $customerId,
            'service_id' => $serviceId,
            'price' => 50.00,
            'expiration' => '2027-01-01',
            'reminder' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/servicetocustomer/update_reminder_status', [
            'id' => $stcId,
            'reminder' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Reminder status updated successfully.']);
    }

    public function test_show_service_details_is_accessible()
    {
        $user = User::factory()->create();
        $customerId = $this->createCustomer();
        $serviceId = $this->createService();

        $stcId = DB::table('servicetocustomer')->insertGetId([
            'customer_id' => $customerId,
            'service_id' => $serviceId,
            'price' => 50.00,
            'expiration' => '2027-01-01',
            'reminder' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/servicetocustomer/'.$stcId.'/details');

        $response->assertStatus(200);
    }
}
