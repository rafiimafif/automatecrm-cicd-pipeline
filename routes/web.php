<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DealsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\ServicetoCustomerController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [HomeController::class, 'dashboard'])->middleware('auth');
Route::get('/welcome', [HomeController::class, 'index'])->name('home');
Route::get('/logout', [HomeController::class, 'logout'])->name('logout.custom');

// ---> Transaction Dataset Routes <---
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index')->middleware('auth');
Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create')->middleware('auth');
Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store')->middleware('auth');
Route::get('/import-transactions', [TransactionController::class, 'import'])->name('transactions.import')->middleware('auth');
Route::get('/export-transactions', [TransactionController::class, 'export'])->name('transactions.export')->middleware('auth');
Route::post('/sync-google-sheets', [TransactionController::class, 'syncToGoogle'])->name('transactions.sync_google')->middleware('auth');

/* ------ customers routes ------ */
Route::get('/customers', [CustomersController::class, 'index'])->name('customers.index')->middleware('auth');
Route::get('/customer_edit/{id}', [CustomersController::class, 'edit'])->name('customer_edit')->middleware('auth');
Route::post('/customer_add', [CustomersController::class, 'store'])->middleware('auth');
Route::put('/customer_update/{id}', [CustomersController::class, 'update'])->name('customer.update')->middleware('auth');
Route::delete('/customer_delete/{id}', [CustomersController::class, 'destroy'])->name('customer.delete')->middleware('auth');

/* ------ services routes ------ */
Route::get('/services', [ServicesController::class, 'index'])->name('services.index');
Route::post('/service_add', [ServicesController::class, 'store'])->middleware('auth');
Route::get('/services/{service}/edit', [ServicesController::class, 'edit'])->name('services.edit');
Route::put('/services/{service}', [ServicesController::class, 'update'])->name('services.update');
Route::delete('/services/{service}', [ServicesController::class, 'destroy'])->name('services.destroy');

/* ------ payments routes ------ */
Route::get('/payments', [PaymentsController::class, 'index'])->middleware('auth');
Route::post('/addpayment', [PaymentsController::class, 'store'])->middleware('auth');

/* ------ email routes ------ */
Route::post('/sendmessage', [CustomersController::class, 'sendmessage'])->middleware('auth');

/* ------ servicetocustomer routes ------ */
Route::post('/addservicetocustomer', [ServicetoCustomerController::class, 'store'])->name('addservicetocustomer')->middleware('auth');
Route::get('/servicetocustomer/{servicetocustomer}/edit', [ServicetoCustomerController::class, 'edit'])->name('servicetocustomer.edit');
Route::put('/servicetocustomer/{servicetocustomer}', [ServicetoCustomerController::class, 'update'])->name('servicetocustomer.update');
Route::delete('/servicetocustomer/{servicetocustomer}', 'App\Http\Controllers\ServicetoCustomerController@destroy')->name('servicetocustomer.destroy');
Route::post('/servicetocustomer/update_reminder_status', 'App\Http\Controllers\ServicetoCustomerController@updateReminderStatus')->name('servicetocustomer.update_reminder_status');

Route::post('/service/{id}/renew', [ServicetoCustomerController::class, 'renewService'])->name('service.renew');
Route::get('/servicetocustomer/{servicetocustomer}/details', [ServicetoCustomerController::class, 'showServiceDetails'])->name('servicetocustomer.details');

/* ------ tools routes ------ */
Route::get('/tools', [CustomersController::class, 'showTools'])->name('tools.show');
Route::post('/export-customers', [CustomersController::class, 'exportCustomers'])->name('export.customers');
Route::post('/import-customers', [CustomersController::class, 'importCustomers'])->name('import.customers');

Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity.log');

/* ------ notes routes ------ */
Route::post('/notes', [NotesController::class, 'store'])->name('notes.store')->middleware('auth');
Route::delete('/notes/{note}', [NotesController::class, 'destroy'])->name('notes.destroy')->middleware('auth');

/* ------ tags routes ------ */
Route::get('/tags', [TagsController::class, 'index'])->name('tags.index')->middleware('auth');
Route::post('/tags', [TagsController::class, 'store'])->name('tags.store')->middleware('auth');
Route::delete('/tags/{tag}', [TagsController::class, 'destroy'])->name('tags.destroy')->middleware('auth');
Route::post('/tags/attach', [TagsController::class, 'attach'])->name('tags.attach')->middleware('auth');
Route::post('/tags/detach', [TagsController::class, 'detach'])->name('tags.detach')->middleware('auth');

/* ------ deals routes ------ */
Route::get('/deals', [DealsController::class, 'index'])->name('deals.index')->middleware('auth');
Route::post('/deals', [DealsController::class, 'store'])->name('deals.store')->middleware('auth');
Route::get('/deals/{deal}', [DealsController::class, 'show'])->name('deals.show')->middleware('auth');
Route::put('/deals/{deal}', [DealsController::class, 'update'])->name('deals.update')->middleware('auth');
Route::patch('/deals/{deal}/stage', [DealsController::class, 'updateStage'])->name('deals.updateStage')->middleware('auth');
Route::delete('/deals/{deal}', [DealsController::class, 'destroy'])->name('deals.destroy')->middleware('auth');

/* ------ tasks routes ------ */
Route::get('/tasks', [TasksController::class, 'index'])->name('tasks.index')->middleware('auth');
Route::post('/tasks', [TasksController::class, 'store'])->name('tasks.store')->middleware('auth');
Route::put('/tasks/{task}', [TasksController::class, 'update'])->name('tasks.update')->middleware('auth');
Route::delete('/tasks/{task}', [TasksController::class, 'destroy'])->name('tasks.destroy')->middleware('auth');
