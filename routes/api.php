<?php

use App\Models\customers;
use App\Models\services;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Health check endpoint for Docker & load balancer probes
Route::get('/health', function () {
    $status = ['status' => 'ok', 'timestamp' => now()->toIso8601String()];
    try {
        DB::connection()->getPdo();
        $status['database'] = 'connected';
    } catch (Exception $e) {
        $status['database'] = 'disconnected';

        return response()->json($status, 503);
    }

    return response()->json($status, 200);
});

// Public endpoint for Excel Power Query auto-refresh
Route::get('/transactions', function () {
    return Transaction::orderBy('id', 'asc')
        ->get()
        ->map(fn ($t, $i) => [
            'No' => $i + 1,
            'Sales Number' => $t->sales_number,
            'Bill Number' => $t->bill_number,
            'Sales Date In' => $t->sales_date_in?->format('Y-m-d H:i:s'),
            'Sales Date Out' => $t->sales_date_out?->format('Y-m-d H:i:s'),
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
        ]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('customers', function () {
    return customers::all();
});

Route::get('customers/{id}', function ($id) {
    return customers::find($id);
});

Route::get('customers-service/{id}', function ($id) {
    $customers = DB::table('servicetocustomer')
        ->join('services', 'services.id', '=', 'servicetocustomer.service_id')
        ->join('customers', 'customers.id', '=', 'servicetocustomer.customer_id')
        ->where('customers.id', '=', $id)
        ->select('servicetocustomer.*', 'services.name as service_name', 'customers.id as customer_id', 'customers.fname as customer_fname', 'customers.lname as customer_lname')
        ->get();

    return $customers;
});

Route::get('services', function () {
    return services::all();
});

Route::get('services/{id}', function ($id) {
    return services::find($id);
});

Route::get('servicetocustomer', function () {
    $services = services::all();
    // $customers = Customers::all();

    $customers = DB::table('servicetocustomer')
        ->join('services', 'services.id', '=', 'servicetocustomer.service_id')
        ->join('customers', 'customers.id', '=', 'servicetocustomer.customer_id')
        ->select('servicetocustomer.*', 'services.name as service_name', 'customers.id as customer_id', 'customers.fname as customer_fname', 'customers.lname as customer_lname')
        ->get();

    return $customers;
});
