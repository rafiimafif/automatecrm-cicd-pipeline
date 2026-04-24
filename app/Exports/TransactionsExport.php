<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function collection()
    {
        return Transaction::orderBy('sales_date_in', 'asc')
            ->get()
            ->map(function ($t) {
                return [
                    'Sales Number' => $t->sales_number,
                    'Bill Number' => $t->bill_number,
                    'Sales Date In' => $t->sales_date_in ? $t->sales_date_in->format('Y-m-d H:i:s') : '',
                    'Sales Date Out' => $t->sales_date_out ? $t->sales_date_out->format('Y-m-d H:i:s') : '',
                    'Brand' => $t->brand,
                    'Area' => $t->area,
                    'City' => $t->city,
                    'Branch' => $t->branch,
                    'Visit Purpose' => $t->visit_purpose,
                    'Reguler Member Code' => $t->reguler_member_code,
                    'Reguler Member Name' => $t->reguler_member_name,
                    'Loyalty Member Code' => $t->loyalty_member_code,
                    'Loyalty Member Name' => $t->loyalty_member_name,
                    'Loyalty Member Type' => $t->loyalty_member_type,
                    'Employee Code' => $t->employee_code,
                    'Employee Name' => $t->employee_name,
                    'External Employee Code' => $t->external_employee_code,
                    'External Employee Name' => $t->external_employee_name,
                    'Payment Method' => $t->payment_method,
                    'Parent Payment Method' => $t->parent_payment_method,
                    'Trace Number' => $t->trace_number,
                    'Approval Code' => $t->approval_code,
                    'EDC Terminal ID' => $t->edc_terminal_id,
                    'Bank Name' => $t->bank_name,
                    'Card Number' => $t->card_number,
                    'Additional Info' => $t->additional_info,
                    'Notes' => $t->notes,
                    'MDR' => $t->mdr,
                    'Payment Amount' => $t->payment_amount,
                    'Nett After MDR' => $t->nett_after_mdr,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Sales Number', 'Bill Number', 'Sales Date In', 'Sales Date Out',
            'Brand', 'Area', 'City', 'Branch', 'Visit Purpose',
            'Reguler Member Code', 'Reguler Member Name',
            'Loyalty Member Code', 'Loyalty Member Name', 'Loyalty Member Type',
            'Employee Code', 'Employee Name', 'External Employee Code', 'External Employee Name',
            'Payment Method', 'Parent Payment Method', 'Trace Number',
            'Approval Code', 'EDC Terminal ID', 'Bank Name', 'Card Number',
            'Additional Info', 'Notes', 'MDR', 'Payment Amount', 'Nett After MDR',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold and background color for header row
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF4E73DF']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
