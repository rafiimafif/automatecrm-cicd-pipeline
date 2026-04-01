<!DOCTYPE html>
<html lang="en">
<head>
    @extends('layouts.head')
</head>
<body id="page-top">
    <div id="wrapper">
        <x-sidebar></x-sidebar>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <x-topbar></x-topbar>
                
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Add Manual Transaction</h1>

                    <!-- Display Validation Errors -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">New POS Entry</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('transactions.store') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="sales_number" class="form-label">Sales Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="sales_number" name="sales_number" value="{{ old('sales_number') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="brand" class="form-label">Brand <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="brand" name="brand" value="{{ old('brand', 'iKitchen') }}" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="sales_date_in" class="form-label">Date In <span class="text-danger">*</span></label>
                                        <input type="datetime-local" class="form-control" id="sales_date_in" name="sales_date_in" value="{{ old('sales_date_in') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                        <select class="form-control" id="payment_method" name="payment_method" required>
                                            <option value="">Select Method...</option>
                                            <option value="CASH">Cash</option>
                                            <option value="QRIS BCA">QRIS</option>
                                            <option value="EDC">EDC</option>
                                            <option value="Debit">Debit</option>
                                            <option value="Credit Card">Credit Card</option>
                                            <option value="Transfer">Transfer</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="bank_name" class="form-label">Bank Name</label>
                                        <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name') }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="payment_amount" class="form-label">Payment Amount (Rp) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="payment_amount" name="payment_amount" value="{{ old('payment_amount') }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="mdr" class="form-label">MDR Fee (Rp)</label>
                                        <input type="number" step="0.01" class="form-control" id="mdr" name="mdr" value="{{ old('mdr', 0) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="nett_after_mdr" class="form-label">Nett Amount (Rp)</label>
                                        <input type="number" step="0.01" class="form-control" id="nett_after_mdr" name="nett_after_mdr" value="{{ old('nett_after_mdr') }}">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Transaction</button>
                                <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <x-footer></x-footer>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <x-main_scripts></x-main_scripts>
</body>
</html>
