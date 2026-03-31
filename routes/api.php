<?php

use App\Models\customers;
use App\Models\services;
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
    } catch (\Exception $e) {
        $status['database'] = 'disconnected';

        return response()->json($status, 503);
    }

    return response()->json($status, 200);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('customers', function () {
    return Customers::all();
});

Route::get('customers/{id}', function ($id) {
    return Customers::find($id);
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
    return Services::all();
});

Route::get('services/{id}', function ($id) {
    return Services::find($id);
});

Route::get('servicetocustomer', function () {
    $services = Services::all();
    //$customers = Customers::all();

    $customers = DB::table('servicetocustomer')
        ->join('services', 'services.id', '=', 'servicetocustomer.service_id')
        ->join('customers', 'customers.id', '=', 'servicetocustomer.customer_id')
        ->select('servicetocustomer.*', 'services.name as service_name', 'customers.id as customer_id', 'customers.fname as customer_fname', 'customers.lname as customer_lname')
        ->get();

    return $customers;
});
