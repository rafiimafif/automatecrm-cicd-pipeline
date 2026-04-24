<?php

namespace App\Services;

use Illuminate\Console\Scheduling\Schedule;

class RenewalService
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            (new RenewalService)->checkForServiceRenewals();
        })->daily();
    }

    public function handleRenewal($serviceToCustomerRecord)
    {
        // Your renewal logic here
    }
}
