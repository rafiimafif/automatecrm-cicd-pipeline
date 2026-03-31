<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/api/health');

        $response->assertStatus(200);
    }

    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_from_dashboard()
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->make();
        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_customers()
    {
        $user = User::factory()->make();
        $response = $this->actingAs($user)->get('/customers');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_services()
    {
        $user = User::factory()->make();
        $response = $this->actingAs($user)->get('/services');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_payments()
    {
        $user = User::factory()->make();
        $response = $this->actingAs($user)->get('/payments');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_activity_log()
    {
        $user = User::factory()->make();
        $response = $this->actingAs($user)->get('/activity_log');

        $response->assertStatus(200);
    }

    public function test_api_health_returns_json()
    {
        $response = $this->get('/api/health');

        $response->assertJsonStructure(['status', 'timestamp']);
    }
}
