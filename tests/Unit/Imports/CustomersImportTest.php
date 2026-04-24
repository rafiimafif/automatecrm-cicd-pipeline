<?php

namespace Tests\Unit\Imports;

use App\Imports\CustomersImport;
use App\Models\Customer;
use Tests\TestCase;

class CustomersImportTest extends TestCase
{
    public function test_customers_import_maps_row_to_model()
    {
        $row = [
            'John',          // fname
            'Doe',           // lname
            'ACME Corp',     // company
            '123 Street',    // address
            '555-0199',      // phone
            'john@acme.com', // email
        ];

        $import = new CustomersImport();
        $model = $import->model($row);

        $this->assertInstanceOf(Customer::class, $model);
        $this->assertEquals('John', $model->fname);
        $this->assertEquals('Doe', $model->lname);
        $this->assertEquals('ACME Corp', $model->company);
        $this->assertEquals('123 Street', $model->address);
        $this->assertEquals('555-0199', $model->phone);
        $this->assertEquals('john@acme.com', $model->email);
    }
}
