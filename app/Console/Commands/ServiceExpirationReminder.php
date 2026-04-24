<?php

namespace App\Console\Commands;

use App\Mail\ExpirationReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Mail;

class ServiceExpirationReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:ServiceExpirationReminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        $services = DB::table('servicetocustomer')
            ->join('customers', 'customers.id', '=', 'servicetocustomer.customer_id')
            ->join('services', 'services.id', '=', 'servicetocustomer.service_id')
            ->where('reminder', '=', '1')
            ->select('servicetocustomer.*', 'customers.fname as customer_name', 'services.name as service_name', 'customers.email as email')
            ->get();

        foreach ($services as $s) {

            $expDate = substr($s->expiration, 0, 10);
            if ($expDate == Carbon::now()->addDays(30)->format('Y-m-d')) {
                $this->info('30 days expiration reminder sent for: '.$s->email);
                $data = [
                    'email' => $s->email,
                    'customer_name' => $s->customer_name,
                    'service_name' => $s->service_name,
                    'expiration' => $s->expiration,
                ];
                Mail::to($s->email)->send(new ExpirationReminder($data));

            } elseif ($expDate == Carbon::now()->addDays(15)->format('Y-m-d')) {
                $this->info('15 days expiration reminder sent for: '.$s->email);
                $data = [
                    'email' => $s->email,
                    'customer_name' => $s->customer_name,
                    'service_name' => $s->service_name,
                    'expiration' => $s->expiration,
                ];
                Mail::to($s->email)->send(new ExpirationReminder($data));
            } elseif ($expDate == Carbon::now()->addDays(5)->format('Y-m-d')) {
                $this->info('5 days expiration reminder sent for: '.$s->email);
                $data = [
                    'email' => $s->email,
                    'customer_name' => $s->customer_name,
                    'service_name' => $s->service_name,
                    'expiration' => $s->expiration,
                ];
                Mail::to($s->email)->send(new ExpirationReminder($data));
            } elseif ($expDate == Carbon::now()->format('Y-m-d')) {
                $this->info('Today expiration reminder sent for: '.$s->email);
                $data = [
                    'email' => $s->email,
                    'customer_name' => $s->customer_name,
                    'service_name' => $s->service_name,
                    'expiration' => $s->expiration,
                ];
                Mail::to($s->email)->send(new ExpirationReminder($data));
            }

        }

        return Command::SUCCESS;
    }
}
