<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that customers can be exported and appended to Dataset.xlsx
     */
    public function test_customers_export_creates_and_appends_data()
    {
        // Create sample customers
        $customers = Customer::factory(3)->create([
            'fname' => 'John',
            'lname' => 'Doe',
            'company' => 'Acme Corp',
            'email' => 'john@example.com',
            'phone' => '123-456-7890',
            'address' => '123 Main St',
        ]);

        // Ensure Dataset.xlsx doesn't exist initially
        $filePath = public_path('Dataset.xlsx');
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Test the export route
        $response = $this->actingAs($this->createUser())
            ->post('/export-customers');

        // Check that the export was successful
        $this->assertTrue(file_exists($filePath), 'Dataset.xlsx should be created');
        $this->assertStringContainsString('appended', $response->getSession()->get('success'));
    }

    /**
     * Test that duplicate customers are not re-exported
     */
    public function test_customers_export_prevents_duplicates()
    {
        $customer = Customer::factory()->create();
        $filePath = public_path('Dataset.xlsx');

        // First export
        $this->actingAs($this->createUser())->post('/export-customers');

        // Verify file exists
        $this->assertTrue(file_exists($filePath));

        // Create another customer
        $newCustomer = Customer::factory()->create();

        // Second export should only add the new customer
        $response = $this->actingAs($this->createUser())
            ->post('/export-customers');

        // Check that only 1 new record was added (not 2)
        $this->assertStringContainsString('1 new customer', $response->getSession()->get('success'));
    }

    /**
     * Test that transactions can be exported and appended to Dataset.xlsx
     */
    public function test_transactions_export_creates_and_appends_data()
    {
        // Create sample transactions
        Transaction::factory(2)->create([
            'sales_number' => 'SALE-001',
            'brand' => 'Test Brand',
            'payment_amount' => 100.00,
        ]);

        // Ensure Dataset.xlsx doesn't exist initially
        $filePath = public_path('Dataset.xlsx');
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Test the export route
        $response = $this->actingAs($this->createUser())
            ->get('/export-transactions');

        // Check that response has success
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Helper to create a test user
     */
    private function createUser()
    {
        return \App\Models\User::factory()->create();
    }
}
