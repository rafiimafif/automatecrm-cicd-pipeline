<?php

namespace App\Services;

use App\Models\Transaction;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Log;

class ExcelSyncService
{
    /**
     * Transaction column headers (A-AE). Column A is "No" (row number).
     */
    private static array $headers = [
        'A' => 'No', 'B' => 'Sales Number', 'C' => 'Bill Number', 'D' => 'Sales Date In',
        'E' => 'Sales Date Out', 'F' => 'Brand', 'G' => 'Area', 'H' => 'City',
        'I' => 'Branch', 'J' => 'Visit Purpose', 'K' => 'Reguler Member Code',
        'L' => 'Reguler Member Name', 'M' => 'Loyalty Member Code',
        'N' => 'Loyalty Member Name', 'O' => 'Loyalty Member Type',
        'P' => 'Employee Code', 'Q' => 'Employee Name',
        'R' => 'External Employee Code', 'S' => 'External Employee Name',
        'T' => 'Payment Method', 'U' => 'Parent Payment Method',
        'V' => 'Trace Number', 'W' => 'Approval Code', 'X' => 'EDC Terminal ID',
        'Y' => 'Bank Name', 'Z' => 'Card Number', 'AA' => 'Additional Info',
        'AB' => 'Notes', 'AC' => 'MDR', 'AD' => 'Payment Amount', 'AE' => 'Nett After MDR',
    ];

    /**
     * Append a single transaction to Dataset.xlsx at the bottom of the sheet.
     * Called automatically by TransactionObserver on create.
     */
    public static function appendTransaction(Transaction $transaction): bool
    {
        $filePath = public_path('Dataset.xlsx');

        try {
            ini_set('memory_limit', '512M');

            libxml_use_internal_errors(true);

            $spreadsheet = null;

            // Load existing file or create a new one
            if (file_exists($filePath) && filesize($filePath) > 0) {
                try {
                    $reader = IOFactory::createReaderForFile($filePath);
                    $reader->setReadDataOnly(false);
                    $spreadsheet = $reader->load($filePath);
                } catch (\Exception $e) {
                    $spreadsheet = null;
                }
            }

            if ($spreadsheet === null) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('POS TRX');
                foreach (self::$headers as $col => $label) {
                    $sheet->setCellValue("{$col}1", $label);
                }
            } else {
                $sheet = $spreadsheet->getSheetByName('POS TRX') ?? $spreadsheet->getActiveSheet();
            }

            // Check if this sales_number already exists in column B (prevent duplicates)
            $highestRow = $sheet->getHighestRow();
            for ($row = 2; $row <= $highestRow; $row++) {
                $val = $sheet->getCell("B{$row}")->getValue();
                if ($val === $transaction->sales_number) {
                    // Already in file, skip
                    $spreadsheet->disconnectWorksheets();
                    unset($spreadsheet);
                    return true;
                }
            }

            // Append at the bottom (POS-style: new data goes below)
            $nextRow = $highestRow + 1;
            self::writeTransactionRow($sheet, $nextRow, $transaction);

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($filePath);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $writer);

            Log::info("ExcelSync: Transaction {$transaction->sales_number} appended to Dataset.xlsx (row {$nextRow})");

            return true;
        } catch (\Exception $e) {
            Log::error("ExcelSync: Failed to append transaction {$transaction->sales_number} — " . $e->getMessage());
            return false;
        }
    }

    /**
     * Write a single transaction's data to a specific row.
     * Column A = No (row - 1, since row 2 = No 1).
     */
    private static function writeTransactionRow($sheet, int $row, Transaction $t): void
    {
        $sheet->setCellValue("A{$row}", $row - 1); // No
        $sheet->setCellValue("B{$row}", $t->sales_number);
        $sheet->setCellValue("C{$row}", $t->bill_number);
        $sheet->setCellValue("D{$row}", $t->sales_date_in ? $t->sales_date_in->format('Y-m-d H:i:s') : '');
        $sheet->setCellValue("E{$row}", $t->sales_date_out ? $t->sales_date_out->format('Y-m-d H:i:s') : '');
        $sheet->setCellValue("F{$row}", $t->brand);
        $sheet->setCellValue("G{$row}", $t->area);
        $sheet->setCellValue("H{$row}", $t->city);
        $sheet->setCellValue("I{$row}", $t->branch);
        $sheet->setCellValue("J{$row}", $t->visit_purpose);
        $sheet->setCellValue("K{$row}", $t->reguler_member_code);
        $sheet->setCellValue("L{$row}", $t->reguler_member_name);
        $sheet->setCellValue("M{$row}", $t->loyalty_member_code);
        $sheet->setCellValue("N{$row}", $t->loyalty_member_name);
        $sheet->setCellValue("O{$row}", $t->loyalty_member_type);
        $sheet->setCellValue("P{$row}", $t->employee_code);
        $sheet->setCellValue("Q{$row}", $t->employee_name);
        $sheet->setCellValue("R{$row}", $t->external_employee_code);
        $sheet->setCellValue("S{$row}", $t->external_employee_name);
        $sheet->setCellValue("T{$row}", $t->payment_method);
        $sheet->setCellValue("U{$row}", $t->parent_payment_method);
        $sheet->setCellValue("V{$row}", $t->trace_number);
        $sheet->setCellValue("W{$row}", $t->approval_code);
        $sheet->setCellValue("X{$row}", $t->edc_terminal_id);
        $sheet->setCellValue("Y{$row}", $t->bank_name);
        $sheet->setCellValue("Z{$row}", $t->card_number);
        $sheet->setCellValue("AA{$row}", $t->additional_info);
        $sheet->setCellValue("AB{$row}", $t->notes);
        $sheet->setCellValue("AC{$row}", $t->mdr);
        $sheet->setCellValue("AD{$row}", $t->payment_amount);
        $sheet->setCellValue("AE{$row}", $t->nett_after_mdr);
    }

    /**
     * Full sync: append ALL transactions not yet in file to the bottom.
     * Used by the manual /export-transactions route.
     */
    public static function syncAllTransactions(): array
    {
        $filePath = public_path('Dataset.xlsx');

        try {
            ini_set('memory_limit', '512M');
            set_time_limit(0);

            libxml_use_internal_errors(true);

            $spreadsheet = null;

            if (file_exists($filePath) && filesize($filePath) > 0) {
                try {
                    $reader = IOFactory::createReaderForFile($filePath);
                    $reader->setReadDataOnly(false);
                    $spreadsheet = $reader->load($filePath);
                } catch (\Exception $e) {
                    $spreadsheet = null;
                }
            }

            if ($spreadsheet === null) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('POS TRX');
                foreach (self::$headers as $col => $label) {
                    $sheet->setCellValue("{$col}1", $label);
                }
            } else {
                $sheet = $spreadsheet->getSheetByName('POS TRX') ?? $spreadsheet->getActiveSheet();
            }

            // Collect existing sales_numbers from column B (column A is now "No")
            $highestRow = $sheet->getHighestRow();
            $existingKeys = [];
            for ($row = 2; $row <= $highestRow; $row++) {
                $val = $sheet->getCell("B{$row}")->getValue();
                if ($val) {
                    $existingKeys[$val] = true;
                }
            }

            // Get only NEW transactions not in the file, ordered oldest first (bottom = newest)
            $newTransactions = Transaction::orderBy('sales_date_in', 'asc')
                ->get()
                ->filter(fn($t) => !isset($existingKeys[$t->sales_number]))
                ->values();

            $newCount = $newTransactions->count();

            if ($newCount > 0) {
                // Append at bottom
                $nextRow = $highestRow + 1;
                foreach ($newTransactions as $t) {
                    self::writeTransactionRow($sheet, $nextRow, $t);
                    $nextRow++;
                }
            }

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($filePath);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $writer);

            return [
                'success' => true,
                'newCount' => $newCount,
                'message' => "Dataset.xlsx updated — {$newCount} new transaction(s) appended at bottom.",
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'newCount' => 0,
                'message' => 'Export failed: ' . class_basename($e) . ' — ' . substr($e->getMessage(), 0, 200),
            ];
        }
    }
}
