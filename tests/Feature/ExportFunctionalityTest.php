<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ExportFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that customers can be exported
     */
    public function test_customers_export_is_triggered()
    {
        Excel::fake();

        // Create sample customers
        Customer::factory(3)->create();

        // Test the export route
        $this->actingAs($this->createUser())
            ->post('/export-customers')
            ->assertStatus(200);

        // Assert that the export was triggered
        Excel::assertDownloaded('customers.xlsx');
    }

    /**
     * Test that transactions can be exported
     */
    public function test_transactions_export_is_triggered()
    {
        Excel::fake();

        // Create sample transactions
        Transaction::factory(2)->create();

        // Test the export route
        $this->actingAs($this->createUser())
            ->get('/export-transactions')
            ->assertStatus(200);

        // Assert that the export was triggered
        Excel::assertDownloaded('transactions.xlsx');
    }

    /**
     * Helper to create a test user
     */
    private function createUser()
    {
        return User::factory()->create();
    }
}
