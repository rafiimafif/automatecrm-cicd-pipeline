<?php

namespace Tests\Unit;

use App\Exports\TransactionsExport;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_transactions_export_collection_and_mapping()
    {
        Transaction::factory()->create([
            'sales_number' => 'EXP-001',
            'brand' => 'ExportBrand',
            'payment_amount' => 1000
        ]);

        $export = new TransactionsExport();
        $collection = $export->collection();

        $this->assertCount(1, $collection);
        
        $first = $collection->first();
        $this->assertArrayHasKey('Sales Number', $first);
        $this->assertEquals('EXP-001', $first['Sales Number']);
        $this->assertEquals('ExportBrand', $first['Brand']);
        $this->assertEquals(1000, $first['Payment Amount']);
    }

    public function test_transactions_export_headings_and_styles()
    {
        $export = new TransactionsExport();
        $headings = $export->headings();
        
        $this->assertContains('Sales Number', $headings);
        $this->assertContains('Payment Amount', $headings);
        
        // Cannot easily mock Worksheet without full PhpSpreadsheet setup, 
        // but we can pass a dummy or assert it returns an array
        $styles = $export->styles(new Worksheet());
        $this->assertIsArray($styles);
        $this->assertArrayHasKey(1, $styles);
        $this->assertArrayHasKey('font', $styles[1]);
    }
}
