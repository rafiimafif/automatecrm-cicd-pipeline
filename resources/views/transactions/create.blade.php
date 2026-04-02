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

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('transactions.store') }}">
                        @csrf

                        {{-- ── Section 1: Transaction Identity ── --}}
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Transaction Identity</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label>Sales Number <span class="text-danger">*</span></label>
                                        <input type="text" name="sales_number" class="form-control @error('sales_number') is-invalid @enderror" value="{{ old('sales_number') }}" required>
                                        @error('sales_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Bill Number</label>
                                        <input type="text" name="bill_number" class="form-control" value="{{ old('bill_number') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Brand <span class="text-danger">*</span></label>
                                        <select name="brand" class="form-control @error('brand') is-invalid @enderror" required>
                                            <option value="iKitchen" {{ old('brand','iKitchen')=='iKitchen'?'selected':'' }}>iKitchen</option>
                                        </select>
                                        @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label>Sales Date In <span class="text-danger">*</span></label>
                                        <input type="datetime-local" name="sales_date_in" class="form-control @error('sales_date_in') is-invalid @enderror" value="{{ old('sales_date_in') }}" required>
                                        @error('sales_date_in')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>Sales Date Out</label>
                                        <input type="datetime-local" name="sales_date_out" class="form-control" value="{{ old('sales_date_out') }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>Visit Purpose</label>
                                        <select name="visit_purpose" class="form-control">
                                            <option value="">— Select —</option>
                                            <option value="DINE IN"  {{ old('visit_purpose')=='DINE IN'?'selected':'' }}>DINE IN</option>
                                            <option value="TAKE AWAY" {{ old('visit_purpose')=='TAKE AWAY'?'selected':'' }}>TAKE AWAY</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label>Area</label>
                                        <input type="text" name="area" class="form-control" value="{{ old('area') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>City</label>
                                        <select name="city" class="form-control">
                                            <option value="">— Select —</option>
                                            <option value="Tangerang Selatan" {{ old('city')=='Tangerang Selatan'?'selected':'' }}>Tangerang Selatan</option>
                                            <option value="Bandung" {{ old('city')=='Bandung'?'selected':'' }}>Bandung</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Branch</label>
                                        <select name="branch" class="form-control">
                                            <option value="">— Select —</option>
                                            <option value="iKitchen" {{ old('branch','iKitchen')=='iKitchen'?'selected':'' }}>iKitchen</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Section 2: Member Info ── --}}
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Member Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Reguler Member Code</label>
                                        <input type="text" name="reguler_member_code" class="form-control" value="{{ old('reguler_member_code', 'Non Member') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Reguler Member Name</label>
                                        <input type="text" name="reguler_member_name" class="form-control" value="{{ old('reguler_member_name', 'Non Member') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label>Loyalty Member Code</label>
                                        <input type="text" name="loyalty_member_code" class="form-control" value="{{ old('loyalty_member_code', 'Non Member') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Loyalty Member Name</label>
                                        <input type="text" name="loyalty_member_name" class="form-control" value="{{ old('loyalty_member_name', 'Non Member') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Loyalty Member Type</label>
                                        <select name="loyalty_member_type" class="form-control">
                                            <option value="Non Member" {{ old('loyalty_member_type','Non Member')=='Non Member'?'selected':'' }}>Non Member</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Section 3: Employee Info ── --}}
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Employee Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Employee Code</label>
                                        <input type="text" name="employee_code" class="form-control" value="{{ old('employee_code', '-') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Employee Name</label>
                                        <input type="text" name="employee_name" class="form-control" value="{{ old('employee_name', '-') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>External Employee Code</label>
                                        <input type="text" name="external_employee_code" class="form-control" value="{{ old('external_employee_code', '-') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>External Employee Name</label>
                                        <input type="text" name="external_employee_name" class="form-control" value="{{ old('external_employee_name', '-') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Section 4: Payment Info ── --}}
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Payment Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Payment Method <span class="text-danger">*</span></label>
                                        <select name="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                                            <option value="">— Select —</option>
                                            @foreach(['CASH','QRIS','QRIS BCA','DEBIT CARD','Transfer','DANA ESB ORDER','GOPAY ESB ORDER','OVO ESB ORDER','SHOPEEPAY ESB ORDER'] as $pm)
                                                <option value="{{ $pm }}" {{ old('payment_method')==$pm?'selected':'' }}>{{ $pm }}</option>
                                            @endforeach
                                        </select>
                                        @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Parent Payment Method</label>
                                        <input type="text" name="parent_payment_method" class="form-control" value="{{ old('parent_payment_method', '-') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label>Trace Number</label>
                                        <input type="text" name="trace_number" class="form-control" value="{{ old('trace_number') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Approval Code</label>
                                        <input type="text" name="approval_code" class="form-control" value="{{ old('approval_code') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>EDC Terminal ID</label>
                                        <input type="text" name="edc_terminal_id" class="form-control" value="{{ old('edc_terminal_id') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label>Bank Name</label>
                                        <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Card Number</label>
                                        <input type="text" name="card_number" class="form-control" value="{{ old('card_number') }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Additional Info</label>
                                        <input type="text" name="additional_info" class="form-control" value="{{ old('additional_info') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Notes</label>
                                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Section 5: Financials ── --}}
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Financials</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label>Payment Amount (Rp) <span class="text-danger">*</span></label>
                                        <input type="number" step="1" name="payment_amount" class="form-control @error('payment_amount') is-invalid @enderror" value="{{ old('payment_amount') }}" required>
                                        @error('payment_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>MDR (Rp)</label>
                                        <input type="number" step="0.01" name="mdr" class="form-control" value="{{ old('mdr', 0) }}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Nett After MDR (Rp)</label>
                                        <input type="number" step="0.01" name="nett_after_mdr" class="form-control" value="{{ old('nett_after_mdr') }}" id="nett_after_mdr">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Transaction</button>
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
                    </form>
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
