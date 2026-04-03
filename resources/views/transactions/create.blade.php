<!DOCTYPE html>
<html lang="en">
<head>
    @extends('layouts.head')
    <style>
        .form-section { margin-bottom: 1rem; }
        .form-section .card { margin-bottom: 0.75rem; box-shadow: 0 0.15rem 0.35rem rgba(58, 59, 69, 0.15); }
        .form-section .card-header { padding: 0.5rem 1rem; background-color: #f8f9fa; border-bottom: 1px solid #e3e6f0; }
        .form-section .card-header h6 { margin: 0; font-size: 0.85rem; }
        .form-section .card-body { padding: 0.75rem; }
        .form-group { margin-bottom: 0.5rem; }
        .form-group label { font-size: 0.85rem; margin-bottom: 0.25rem; font-weight: 500; }
        .form-control { font-size: 0.85rem; padding: 0.35rem 0.5rem; height: auto; border-radius: 0.25rem; }
        .btn-sm-form { font-size: 0.75rem; padding: 0.3rem 0.6rem; }
        .row { margin: -0.25rem; }
        .col-md-2, .col-md-3, .col-md-4, .col-md-6 { padding: 0.25rem; }
        .accordion .btn-link { padding-left: 0; font-size: 0.85rem; font-weight: 500; color: #004085; }
        .accordion .btn-link:hover { text-decoration: underline; }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <x-sidebar></x-sidebar>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <x-topbar></x-topbar>

                <div class="container-fluid p-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h4 text-gray-800">Add Manual Transaction</h2>
                        <small class="text-muted">Fill required fields (<span class="text-danger">*</span>)</small>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-2" role="alert">
                            <small>
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="font-size: 1rem;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </small>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('transactions.store') }}">
                        @csrf

                        {{-- ── SECTION: CORE INFO (Required Fields) ── --}}
                        <div class="form-section">
                            <div class="card">
                                <div class="card-header">
                                    <h6>Core Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Sales Number <span class="text-danger">*</span></label>
                                                <input type="text" name="sales_number" class="form-control @error('sales_number') is-invalid @enderror" value="{{ old('sales_number') }}" required>
                                                @error('sales_number')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Bill Number</label>
                                                <input type="text" name="bill_number" class="form-control" value="{{ old('bill_number') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Brand <span class="text-danger">*</span></label>
                                                <select name="brand" class="form-control @error('brand') is-invalid @enderror" required>
                                                    <option value="iKitchen" {{ old('brand','iKitchen')=='iKitchen'?'selected':'' }}>iKitchen</option>
                                                </select>
                                                @error('brand')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Visit Purpose</label>
                                                <select name="visit_purpose" class="form-control">
                                                    <option value="">— Select —</option>
                                                    <option value="DINE IN"  {{ old('visit_purpose')=='DINE IN'?'selected':'' }}>DINE IN</option>
                                                    <option value="TAKE AWAY" {{ old('visit_purpose')=='TAKE AWAY'?'selected':'' }}>TAKE AWAY</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Payment Method <span class="text-danger">*</span></label>
                                                <select name="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                                                    <option value="">— Select —</option>
                                                    @foreach(['CASH','QRIS','QRIS BCA','DEBIT CARD','Transfer','DANA ESB ORDER','GOPAY ESB ORDER','OVO ESB ORDER','SHOPEEPAY ESB ORDER'] as $pm)
                                                        <option value="{{ $pm }}" {{ old('payment_method')==$pm?'selected':'' }}>{{ $pm }}</option>
                                                    @endforeach
                                                </select>
                                                @error('payment_method')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Sales Date In <span class="text-danger">*</span></label>
                                                <input type="datetime-local" name="sales_date_in" class="form-control @error('sales_date_in') is-invalid @enderror" value="{{ old('sales_date_in') }}" required>
                                                @error('sales_date_in')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Sales Date Out</label>
                                                <input type="datetime-local" name="sales_date_out" class="form-control" value="{{ old('sales_date_out') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Payment Amount (Rp) <span class="text-danger">*</span></label>
                                                <input type="number" step="1" name="payment_amount" class="form-control @error('payment_amount') is-invalid @enderror" value="{{ old('payment_amount') }}" required>
                                                @error('payment_amount')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>MDR (Rp)</label>
                                                <input type="number" step="0.01" name="mdr" class="form-control" value="{{ old('mdr', 0) }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>City</label>
                                                <select name="city" class="form-control">
                                                    <option value="">— Select —</option>
                                                    <option value="Tangerang Selatan" {{ old('city')=='Tangerang Selatan'?'selected':'' }}>Tangerang Selatan</option>
                                                    <option value="Bandung" {{ old('city')=='Bandung'?'selected':'' }}>Bandung</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Branch</label>
                                                <select name="branch" class="form-control">
                                                    <option value="">— Select —</option>
                                                    <option value="iKitchen" {{ old('branch','iKitchen')=='iKitchen'?'selected':'' }}>iKitchen</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Area</label>
                                                <input type="text" name="area" class="form-control" value="{{ old('area') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Nett After MDR (Rp)</label>
                                                <input type="number" step="0.01" name="nett_after_mdr" class="form-control" value="{{ old('nett_after_mdr') }}" id="nett_after_mdr">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Notes</label>
                                                <textarea name="notes" class="form-control" rows="1">{{ old('notes') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── SECTION: OPTIONAL/ADVANCED FIELDS (Collapsible) ── --}}
                        <div class="form-section">
                            <div id="accordion">
                                <div class="card border-0">
                                    <div class="card-header bg-white border-0 p-0">
                                        <a class="btn btn-link btn-block text-left p-2" data-toggle="collapse" href="#advancedFields" role="button" aria-expanded="false" aria-controls="advancedFields">
                                            <small><i class="fas fa-chevron-down"></i> Additional Fields (Members, Employees, Payment Details)</small>
                                        </a>
                                    </div>
                                    <div id="advancedFields" class="collapse" data-parent="#accordion">
                                        <div class="card-body p-2">
                                            {{-- Member Info --}}
                                            <div class="card border-light">
                                                <div class="card-header bg-light p-2">
                                                    <small class="font-weight-bold">Member Information</small>
                                                </div>
                                                <div class="card-body p-2">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>Reguler Member Code</label>
                                                                <input type="text" name="reguler_member_code" class="form-control" value="{{ old('reguler_member_code', 'Non Member') }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>Reguler Member Name</label>
                                                                <input type="text" name="reguler_member_name" class="form-control" value="{{ old('reguler_member_name', 'Non Member') }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label>Loyalty Member Code</label>
                                                                <input type="text" name="loyalty_member_code" class="form-control" value="{{ old('loyalty_member_code', 'Non Member') }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label>Loyalty Member Name</label>
                                                                <input type="text" name="loyalty_member_name" class="form-control" value="{{ old('loyalty_member_name', 'Non Member') }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label>Loyalty Member Type</label>
                                                                <select name="loyalty_member_type" class="form-control">
                                                                    <option value="Non Member" {{ old('loyalty_member_type','Non Member')=='Non Member'?'selected':'' }}>Non Member</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Employee Info --}}
                                            <div class="card border-light mt-1">
                                                <div class="card-header bg-light p-2">
                                                    <small class="font-weight-bold">Employee Information</small>
                                                </div>
                                                <div class="card-body p-2">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>Employee Code</label>
                                                                <input type="text" name="employee_code" class="form-control" value="{{ old('employee_code', '-') }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>Employee Name</label>
                                                                <input type="text" name="employee_name" class="form-control" value="{{ old('employee_name', '-') }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>External Employee Code</label>
                                                                <input type="text" name="external_employee_code" class="form-control" value="{{ old('external_employee_code', '-') }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>External Employee Name</label>
                                                                <input type="text" name="external_employee_name" class="form-control" value="{{ old('external_employee_name', '-') }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Payment Details --}}
                                            <div class="card border-light mt-1">
                                                <div class="card-header bg-light p-2">
                                                    <small class="font-weight-bold">Payment Details</small>
                                                </div>
                                                <div class="card-body p-2">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label>Parent Payment Method</label>
                                                                <input type="text" name="parent_payment_method" class="form-control" value="{{ old('parent_payment_method', '-') }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label>Trace Number</label>
                                                                <input type="text" name="trace_number" class="form-control" value="{{ old('trace_number') }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label>Approval Code</label>
                                                                <input type="text" name="approval_code" class="form-control" value="{{ old('approval_code') }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label>EDC Terminal ID</label>
                                                                <input type="text" name="edc_terminal_id" class="form-control" value="{{ old('edc_terminal_id') }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label>Bank Name</label>
                                                                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-1">
                                                            <div class="form-group">
                                                                <label>Card</label>
                                                                <input type="text" name="card_number" class="form-control" placeholder="Last 4" value="{{ old('card_number') }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>Additional Info</label>
                                                                <input type="text" name="additional_info" class="form-control" value="{{ old('additional_info') }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Save Transaction</button>
                            <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Cancel</a>
                        </div>
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
    <script>
        // Auto-calculate Nett After MDR
        document.querySelector('input[name="payment_amount"]')?.addEventListener('change', calculateNett);
        document.querySelector('input[name="mdr"]')?.addEventListener('change', calculateNett);
        
        function calculateNett() {
            const payment = parseFloat(document.querySelector('input[name="payment_amount"]').value) || 0;
            const mdr = parseFloat(document.querySelector('input[name="mdr"]').value) || 0;
            const nett = payment - mdr;
            document.getElementById('nett_after_mdr').value = nett.toFixed(2);
        }
    </script>
</body>
</html>
