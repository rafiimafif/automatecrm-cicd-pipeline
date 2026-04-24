<?php

namespace Tests\Unit;

use App\Exports\CustomersExport;
use App\Imports\CustomersImport;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomersImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_import()
    {
        $import = new CustomersImport();
        $model = $import->model([
            'John',
            'Doe',
            'Acme',
            '123 St',
            '12345',
            'john@acme.com'
        ]);

        $this->assertInstanceOf(Customer::class, $model);
        $this->assertEquals('John', $model->fname);
        $this->assertEquals('Doe', $model->lname);
        $this->assertEquals('john@acme.com', $model->email);
    }

    public function test_customers_export()
    {
        Customer::factory()->create([
            'fname' => 'John',
            'lname' => 'Doe',
            'email' => 'john@acme.com'
        ]);

        $export = new CustomersExport();
        $collection = $export->collection();
        
        $this->assertCount(1, $collection);
        $this->assertEquals('John', $collection->first()->fname);

        $headings = $export->headings();
        $this->assertEquals(['ID', 'Name', 'Email'], $headings);
    }
}
