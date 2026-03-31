<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicetoCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_example()
    {
        $response = $this->get('/api/health');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_add_customer()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/customer_add', [
            'fname' => 'John',
            'lname' => 'Doe',
            'email' => uniqid('john', true).'@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'company' => 'TestCo',
        ]);

        $response->assertRedirect('/customers');
    }

    public function test_authenticated_user_can_add_service()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/service_add', [
            'name' => uniqid('Service', true),
            'description' => 'A test service',
        ]);

        $response->assertRedirect('/services');
    }

    public function test_logout_redirects_to_login()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/logout');

        $response->assertRedirect('/login');
    }
}
