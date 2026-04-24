<?php

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_correct_data()
    {
        $user = User::factory()->create();

        // Create some data
        Transaction::create([
            'sales_number' => 'TEST-001',
            'payment_amount' => 1000,
            'mdr' => 10,
            'nett_after_mdr' => 990,
            'brand' => 'Visa',
            'payment_method' => 'Credit Card',
            'sales_date_in' => now(),
            'city' => 'Jakarta',
        ]);

        $customer = Customer::factory()->create(['created_at' => now()]);

        $stage = DealStage::create(['name' => 'Initial', 'order' => 1]);
        Deal::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'open',
            'value' => 5000,
            'deal_stage_id' => $stage->id,
        ]);

        Task::factory()->create([
            'taskable_id' => $customer->id,
            'taskable_type' => Customer::class,
            'status' => 'pending',
            'due_date' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('totalPayment', 1000);
        $response->assertViewHas('totalCustomers', 1);
        $response->assertViewHas('openDeals', 1);
        $response->assertViewHas('overdueTasks', 1);
    }

    public function test_finance_static_method()
    {
        Transaction::create([
            'sales_number' => 'TEST-002',
            'payment_amount' => 2000,
            'mdr' => 20,
            'nett_after_mdr' => 1980,
            'brand' => 'Mastercard',
            'payment_method' => 'Debit',
            'sales_date_in' => now(),
            'city' => 'Surabaya',
        ]);

        $finance = HomeController::finance();

        $this->assertEquals(2000, $finance[0]);
        $this->assertEquals(20, $finance[1]);
        $this->assertEquals(1980, $finance[2]);
        $this->assertEquals(1, $finance[3]);
    }

    public function test_logout_redirects_to_login()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/logout');

        $response->assertRedirect('login');
        $this->assertFalse(Auth::check());
    }
}
