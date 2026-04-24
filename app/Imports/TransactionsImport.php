<?php

namespace App\Imports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TransactionsImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'POS TRX' => new PosTransactionsSheet(),
        ];
    }
}

class PosTransactionsSheet implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow
{
    public function model(array $row)
    {
        // Skip empty rows or rows without a sales number
        if (empty($row['sales_number'] ?? null)) {
            return null;
        }

        // Parse Excel numeric dates to PHP DateTimes
        $salesDateIn = $row['sales_date_in'] ?? null;
        $salesDateOut = $row['sales_date_out'] ?? null;

        $dateIn = ! empty($salesDateIn) && is_numeric($salesDateIn)
            ? Date::excelToDateTimeObject((float) $salesDateIn)
            : null;

        $dateOut = ! empty($salesDateOut) && is_numeric($salesDateOut)
            ? Date::excelToDateTimeObject((float) $salesDateOut)
            : null;

        Transaction::updateOrCreate(
            ['sales_number' => $row['sales_number'] ?? null],
            [
                'bill_number' => $row['bill_number'] ?? null,
                'sales_date_in' => $dateIn,
                'sales_date_out' => $dateOut,
                'brand' => $row['brand'] ?? null,
                'area' => $row['area'] ?? null,
                'city' => $row['city'] ?? null,
                'branch' => $row['branch'] ?? null,
                'visit_purpose' => $row['visit_purpose'] ?? null,
                'reguler_member_code' => $row['reguler_member_code'] ?? null,
                'reguler_member_name' => $row['reguler_member_name'] ?? null,
                'loyalty_member_code' => $row['loyalty_member_code'] ?? null,
                'loyalty_member_name' => $row['loyalty_member_name'] ?? null,
                'loyalty_member_type' => $row['loyalty_member_type'] ?? null,
                'employee_code' => $row['employee_code'] ?? null,
                'employee_name' => $row['employee_name'] ?? null,
                'external_employee_code' => $row['external_employee_code'] ?? null,
                'external_employee_name' => $row['external_employee_name'] ?? null,
                'payment_method' => $row['payment_method'] ?? null,
                'parent_payment_method' => $row['parent_payment_method'] ?? null,
                'trace_number' => $row['trace_number'] ?? null,
                'approval_code' => $row['approval_code'] ?? null,
                'edc_terminal_id' => $row['edc_terminal_id'] ?? null,
                'bank_name' => $row['bank_name'] ?? null,
                'card_number' => $row['card_number'] ?? null,
                'additional_info' => $row['additional_info'] ?? null,
                'notes' => $row['notes'] ?? null,
                'mdr' => $row['mdr'] ?? 0,
                'payment_amount' => $row['payment_amount'] ?? 0,
                'nett_after_mdr' => $row['nett_after_mdr'] ?? 0,
            ]
        );

        return null; // updateOrCreate handles insert/update, no need to return a model
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
