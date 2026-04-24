<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Services\GoogleSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createUser()
    {
        return User::factory()->create();
    }

    public function test_index_displays_transactions()
    {
        $user = $this->createUser();
        
        Transaction::factory()->create([
            'sales_number' => 'SALE-12345',
            'brand' => 'Acme Corp'
        ]);

        $response = $this->actingAs($user)->get('/transactions');

        $response->assertStatus(200);
        $response->assertViewIs('transactions.index');
        $response->assertSee('SALE-12345');
    }

    public function test_index_filters_transactions()
    {
        $user = $this->createUser();
        
        Transaction::factory()->create([
            'sales_number' => 'SALE-123',
            'brand' => 'Brand A'
        ]);

        Transaction::factory()->create([
            'sales_number' => 'SALE-456',
            'brand' => 'Brand B'
        ]);

        $response = $this->actingAs($user)->get('/transactions?search=Brand A');

        $response->assertStatus(200);
        $response->assertSee('SALE-123');
        $response->assertDontSee('SALE-456');
    }

    public function test_create_displays_form()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/transactions/create');

        $response->assertStatus(200);
        $response->assertViewIs('transactions.create');
    }

    public function test_store_creates_transaction_and_syncs()
    {
        $user = $this->createUser();

        // Mock GoogleSyncService to prevent actual API calls
        $mockSync = Mockery::mock(GoogleSyncService::class);
        $mockSync->shouldReceive('syncOne')->once()->andReturn(true);
        $this->app->instance(GoogleSyncService::class, $mockSync);

        $data = [
            'sales_number' => 'SALE-999',
            'sales_date_in' => now()->format('Y-m-d'),
            'brand' => 'Test Brand',
            'payment_method' => 'Credit Card',
            'payment_amount' => 1000,
            'mdr' => 10,
        ];

        $response = $this->actingAs($user)->post('/transactions', $data);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'sales_number' => 'SALE-999',
            'payment_amount' => 1000,
            'nett_after_mdr' => 990 // 1000 - 10
        ]);
    }

    public function test_sync_to_google_batches_transactions()
    {
        $user = $this->createUser();
        
        Transaction::factory(2)->create();

        // Mock GoogleSyncService
        $mockSync = Mockery::mock(GoogleSyncService::class);
        $mockSync->shouldReceive('syncBatch')->once()->andReturn(true);
        $this->app->instance(GoogleSyncService::class, $mockSync);

        $response = $this->actingAs($user)->post('/sync-google-sheets');

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');
    }

    public function test_sync_to_google_with_empty_transactions()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/sync-google-sheets');

        $response->assertRedirect();
        $response->assertSessionHas('error', 'No transactions found to sync.');
    }

    public function test_export_downloads_excel()
    {
        $user = $this->createUser();
        Transaction::factory()->create();

        // Using Mockery to mock Excel Facade
        \Maatwebsite\Excel\Facades\Excel::shouldReceive('download')->once()->andReturn(new \Illuminate\Http\Response('excel-data'));

        $response = $this->actingAs($user)->get('/export-transactions');

        $response->assertStatus(200);
        $this->assertEquals('excel-data', $response->getContent());
    }

    public function test_import_processes_excel()
    {
        $user = $this->createUser();

        // Mock GoogleSyncService to prevent actual API calls during import
        $mockSync = Mockery::mock(GoogleSyncService::class);
        $mockSync->shouldReceive('syncBatch')->once()->andReturn(true);
        $this->app->instance(GoogleSyncService::class, $mockSync);

        \Maatwebsite\Excel\Facades\Excel::shouldReceive('import')->once()->andReturn(true);

        $response = $this->actingAs($user)->get('/import-transactions');

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');
    }

    public function test_import_fails_on_exception()
    {
        $user = $this->createUser();

        \Maatwebsite\Excel\Facades\Excel::shouldReceive('import')->andThrow(new \Exception('Import error'));

        $response = $this->actingAs($user)->get('/import-transactions');

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('error');
    }
}
