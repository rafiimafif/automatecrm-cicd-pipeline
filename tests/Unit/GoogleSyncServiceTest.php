<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Services\GoogleSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_one_without_url()
    {
        putenv('GOOGLE_SHEET_WEB_APP_URL=');
        $service = new GoogleSyncService;
        $transaction = Transaction::factory()->create();

        $result = $service->syncOne($transaction);
        $this->assertFalse($result);
    }

    public function test_sync_one_success()
    {
        putenv('GOOGLE_SHEET_WEB_APP_URL=http://example.com/sync');
        Http::fake([
            'http://example.com/sync' => Http::response('Success', 200),
        ]);

        $service = new GoogleSyncService;
        $transaction = Transaction::factory()->create();

        $result = $service->syncOne($transaction);
        $this->assertTrue($result);
    }

    public function test_sync_batch_success()
    {
        putenv('GOOGLE_SHEET_WEB_APP_URL=http://example.com/sync');
        Http::fake([
            'http://example.com/sync' => Http::response('Success', 200),
        ]);

        $service = new GoogleSyncService;
        $transactions = collect([Transaction::factory()->create()]);

        $result = $service->syncBatch($transactions);
        $this->assertTrue($result);
    }
}
