<?php

namespace Tests\Unit\Exports;

use App\Exports\CustomersExport;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomersExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_export_collection()
    {
        Customer::factory()->count(3)->create();

        $export = new CustomersExport();
        $collection = $export->collection();

        $this->assertCount(3, $collection);
        $this->assertInstanceOf(Customer::class, $collection->first());
    }

    public function test_customers_export_headings()
    {
        $export = new CustomersExport();
        $headings = $export->headings();

        $this->assertEquals(['ID', 'Name', 'Email'], $headings);
    }
}
