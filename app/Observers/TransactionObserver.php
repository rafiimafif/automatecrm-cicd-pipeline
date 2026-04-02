<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\ExcelSyncService;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     * Automatically appends the new transaction to Dataset.xlsx (bottom of sheet).
     */
    public function created(Transaction $transaction): void
    {
        ExcelSyncService::appendTransaction($transaction);
    }
}
