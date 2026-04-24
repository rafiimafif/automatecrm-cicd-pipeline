<?php

namespace Tests\Unit;

use App\Imports\PosTransactionsSheet;
use App\Imports\TransactionsImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Tests\TestCase;

class TransactionsImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_sheets()
    {
        $import = new TransactionsImport;
        $sheets = $import->sheets();

        $this->assertArrayHasKey('POS TRX', $sheets);
        $this->assertInstanceOf(PosTransactionsSheet::class, $sheets['POS TRX']);
    }

    public function test_pos_transaction_sheet_model_processing()
    {
        $sheet = new PosTransactionsSheet;

        // Empty row should return null
        $this->assertNull($sheet->model([]));

        // Valid row should insert into DB and return null
        $row = [
            'sales_number' => 'SALE-TEST-1',
            'brand' => 'TestBrand',
            'payment_amount' => 500,
            'mdr' => 10,
            'nett_after_mdr' => 490,
            'sales_date_in' => Date::PHPToExcel(now()),
            'sales_date_out' => Date::PHPToExcel(now()),
        ];

        $sheet->model($row);

        $this->assertDatabaseHas('transactions', [
            'sales_number' => 'SALE-TEST-1',
            'brand' => 'TestBrand',
            'payment_amount' => 500,
        ]);

        $this->assertEquals(500, $sheet->batchSize());
        $this->assertEquals(1000, $sheet->chunkSize());
    }
}
