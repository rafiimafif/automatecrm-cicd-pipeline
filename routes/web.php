<?php

use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'dashboard'])->middleware('auth');
Route::get('/welcome', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/logout', [App\Http\Controllers\HomeController::class, 'logout'])->name('logout.custom');

// ---> Transaction Dataset Routes <---
Route::get('/transactions', [App\Http\Controllers\TransactionController::class, 'index'])->name('transactions.index')->middleware('auth');
Route::get('/transactions/create', [App\Http\Controllers\TransactionController::class, 'create'])->name('transactions.create')->middleware('auth');
Route::post('/transactions', [App\Http\Controllers\TransactionController::class, 'store'])->name('transactions.store')->middleware('auth');
Route::get('/import-transactions', [App\Http\Controllers\TransactionController::class, 'import'])->name('transactions.import')->middleware('auth');
Route::get('/export-transactions', [App\Http\Controllers\TransactionController::class, 'export'])->name('transactions.export')->middleware('auth');
Route::post('/sync-google-sheets', [App\Http\Controllers\TransactionController::class, 'syncToGoogle'])->name('transactions.sync_google')->middleware('auth');

/* ------ customers routes ------ */
Route::get('/customers', [App\Http\Controllers\CustomersController::class, 'index'])->name('customers.index')->middleware('auth');
Route::get('/customer_edit/{id}', [App\Http\Controllers\CustomersController::class, 'edit'])->name('customer_edit')->middleware('auth');
Route::post('/customer_add', [App\Http\Controllers\CustomersController::class, 'store'])->middleware('auth');
Route::put('/customer_update/{id}', [App\Http\Controllers\CustomersController::class, 'update'])->name('customer.update')->middleware('auth');
Route::delete('/customer_delete/{id}', [App\Http\Controllers\CustomersController::class, 'destroy'])->name('customer.delete')->middleware('auth');

/* ------ services routes ------ */
Route::get('/services', [App\Http\Controllers\ServicesController::class, 'index'])->name('services.index');
Route::post('/service_add', [App\Http\Controllers\ServicesController::class, 'store'])->middleware('auth');
Route::get('/services/{service}/edit', [App\Http\Controllers\ServicesController::class, 'edit'])->name('services.edit');
Route::put('/services/{service}', [App\Http\Controllers\ServicesController::class, 'update'])->name('services.update');
Route::delete('/services/{service}', [App\Http\Controllers\ServicesController::class, 'destroy'])->name('services.destroy');

/* ------ payments routes ------ */
Route::get('/payments', [App\Http\Controllers\PaymentsController::class, 'index'])->middleware('auth');
Route::post('/addpayment', [App\Http\Controllers\PaymentsController::class, 'store'])->middleware('auth');

/* ------ email routes ------ */
Route::post('/sendmessage', [App\Http\Controllers\CustomersController::class, 'sendmessage'])->middleware('auth');

/* ------ servicetocustomer routes ------ */
Route::post('/addservicetocustomer', [App\Http\Controllers\ServicetoCustomerController::class, 'store'])->name('addservicetocustomer')->middleware('auth');
Route::get('/servicetocustomer/{servicetocustomer}/edit', [App\Http\Controllers\ServicetoCustomerController::class, 'edit'])->name('servicetocustomer.edit');
Route::put('/servicetocustomer/{servicetocustomer}', [App\Http\Controllers\ServicetoCustomerController::class, 'update'])->name('servicetocustomer.update');
Route::delete('/servicetocustomer/{servicetocustomer}', 'App\Http\Controllers\ServicetoCustomerController@destroy')->name('servicetocustomer.destroy');
Route::post('/servicetocustomer/update_reminder_status', 'App\Http\Controllers\ServicetoCustomerController@updateReminderStatus')->name('servicetocustomer.update_reminder_status');

Route::post('/service/{id}/renew', [App\Http\Controllers\ServicetoCustomerController::class, 'renewService'])->name('service.renew');
Route::get('/servicetocustomer/{servicetocustomer}/details', [App\Http\Controllers\ServicetoCustomerController::class, 'showServiceDetails'])->name('servicetocustomer.details');

/* ------ tools routes ------ */
Route::get('/tools', [App\Http\Controllers\CustomersController::class, 'showTools'])->name('tools.show');
Route::post('/export-customers', [App\Http\Controllers\CustomersController::class, 'exportCustomers'])->name('export.customers');
Route::post('/import-customers', [App\Http\Controllers\CustomersController::class, 'importCustomers'])->name('import.customers');

Route::get('/activity-log', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity.log');

/* ------ notes routes ------ */
Route::post('/notes', [App\Http\Controllers\NotesController::class, 'store'])->name('notes.store')->middleware('auth');
Route::delete('/notes/{note}', [App\Http\Controllers\NotesController::class, 'destroy'])->name('notes.destroy')->middleware('auth');

/* ------ tags routes ------ */
Route::get('/tags', [App\Http\Controllers\TagsController::class, 'index'])->name('tags.index')->middleware('auth');
Route::post('/tags', [App\Http\Controllers\TagsController::class, 'store'])->name('tags.store')->middleware('auth');
Route::delete('/tags/{tag}', [App\Http\Controllers\TagsController::class, 'destroy'])->name('tags.destroy')->middleware('auth');
Route::post('/tags/attach', [App\Http\Controllers\TagsController::class, 'attach'])->name('tags.attach')->middleware('auth');
Route::post('/tags/detach', [App\Http\Controllers\TagsController::class, 'detach'])->name('tags.detach')->middleware('auth');

/* ------ deals routes ------ */
Route::get('/deals', [App\Http\Controllers\DealsController::class, 'index'])->name('deals.index')->middleware('auth');
Route::post('/deals', [App\Http\Controllers\DealsController::class, 'store'])->name('deals.store')->middleware('auth');
Route::get('/deals/{deal}', [App\Http\Controllers\DealsController::class, 'show'])->name('deals.show')->middleware('auth');
Route::put('/deals/{deal}', [App\Http\Controllers\DealsController::class, 'update'])->name('deals.update')->middleware('auth');
Route::patch('/deals/{deal}/stage', [App\Http\Controllers\DealsController::class, 'updateStage'])->name('deals.updateStage')->middleware('auth');
Route::delete('/deals/{deal}', [App\Http\Controllers\DealsController::class, 'destroy'])->name('deals.destroy')->middleware('auth');

/* ------ tasks routes ------ */
Route::get('/tasks', [App\Http\Controllers\TasksController::class, 'index'])->name('tasks.index')->middleware('auth');
Route::post('/tasks', [App\Http\Controllers\TasksController::class, 'store'])->name('tasks.store')->middleware('auth');
Route::put('/tasks/{task}', [App\Http\Controllers\TasksController::class, 'update'])->name('tasks.update')->middleware('auth');
Route::delete('/tasks/{task}', [App\Http\Controllers\TasksController::class, 'destroy'])->name('tasks.destroy')->middleware('auth');
