<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSyncService
{
    /**
     * Sync a single transaction to Google Sheets.
     */
    public function syncOne(Transaction $transaction)
    {
        $url = env('GOOGLE_SHEET_WEB_APP_URL');

        if (! $url) {
            Log::warning('Google Sheet Web App URL not configured.');

            return false;
        }

        // Use the current total count as the row number for real-time sync
        $rowNumber = Transaction::count();
        $row = $this->mapTransactionToRow($transaction, $rowNumber);

        try {
            // Wrap the single row in a 2D array [ [row] ] for Google's setValues compatibility
            // and use the same object structure as syncBatch for consistency.
            $response = Http::timeout(60)->post($url, [
                'clear' => false,
                'rows' => [$row],
            ]);

            $isSuccess = $response->successful() && $response->body() === 'Success';

            if (! $isSuccess) {
                Log::error('Google Sheet Real-Time Sync Failed. Response: '.$response->body());
            }

            return $isSuccess;
        } catch (\Exception $e) {
            Log::error('Google Sheet Sync Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Sync multiple transactions in chunks for maximum efficiency and stability.
     */
    public function syncBatch($transactions)
    {
        $url = env('GOOGLE_SHEET_WEB_APP_URL');

        if (! $url || $transactions->isEmpty()) {
            return false;
        }

        // Chunking ensures we never hit the 30s timeout or memory limits
        $chunks = $transactions->chunk(300); // 300 rows at a time is optimal
        $allSuccess = true;
        $isFirst = true;

        $counter = 1;
        foreach ($chunks as $chunk) {
            $rows = $chunk->map(function ($t) use (&$counter) {
                return $this->mapTransactionToRow($t, $counter++);
            })->toArray();

            try {
                // Bulk write to Google Sheets with 90s timeout
                // For the very first chunk, we send 'clear' => true to wipe existing spreadsheet data
                $response = Http::timeout(90)->post($url, [
                    'clear' => $isFirst,
                    'rows' => $rows,
                ]);

                if (! $response->successful() || $response->body() !== 'Success') {
                    Log::error('Google Sheet Sync Chunk Error: '.$response->body());
                    $allSuccess = false;
                    break;
                }

                // After the first chunk wipes the sheet, subsequent chunks append
                $isFirst = false;
            } catch (\Exception $e) {
                Log::error('Google Sheet Batch Sync Error: '.$e->getMessage());
                $allSuccess = false;
                break;
            }
        }

        return $allSuccess;
    }

    /**
     * Maps the Transaction model to the specific column order of your Google Sheet.
     */
    private function mapTransactionToRow(Transaction $t, $index = '')
    {
        return [
            $index, // Column A: No (Incremental row number)
            $t->sales_number,
            $t->bill_number,
            $t->sales_date_in ? $t->sales_date_in->format('Y-m-d H:i:s') : '',
            $t->sales_date_out ? $t->sales_date_out->format('Y-m-d H:i:s') : '',
            $t->brand,
            $t->area,
            $t->city,
            $t->branch,
            $t->visit_purpose,
            $t->reguler_member_code,
            $t->reguler_member_name,
            $t->loyalty_member_code,
            $t->loyalty_member_name,
            $t->loyalty_member_type,
            $t->employee_code,
            $t->employee_name,
            $t->external_employee_code,
            $t->external_employee_name,
            $t->payment_method,
            $t->parent_payment_method,
            $t->trace_number,
            $t->approval_code,
            $t->edc_terminal_id,
            $t->bank_name,
            $t->card_number,
            $t->additional_info,
            $t->notes,
            (float) ($t->mdr ?? 0),
            (float) ($t->payment_amount ?? 0),
            (float) ($t->nett_after_mdr ?? 0),
        ];
    }
}
