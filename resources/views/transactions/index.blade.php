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
                    <h1 class="h3 mb-4 text-gray-800">All Transactions</h1>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                Transaction List
                                <span class="badge badge-secondary ml-2">{{ $transactions->total() }} records</span>
                            </h6>
                            <div class="d-flex align-items-center">
                                <a href="{{ route('transactions.create') }}" class="btn btn-success btn-sm mr-2">
                                    <i class="fas fa-plus"></i> Add Transaction
                                </a>
                                <a href="{{ route('transactions.export') }}" class="btn btn-warning btn-sm mr-3">
                                    <i class="fas fa-file-excel"></i> Export to Dataset.xlsx
                                </a>
                                <form action="{{ route('transactions.index') }}" method="GET" class="form-inline">
                                    <input type="text" name="search" class="form-control form-control-sm mr-2"
                                        placeholder="Search Sales # / Brand / Payment" value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover mb-0"
                                       style="white-space: nowrap; font-size: 0.82rem;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Sales Number</th>
                                            <th>Bill Number</th>
                                            <th>Sales Date In</th>
                                            <th>Sales Date Out</th>
                                            <th>Brand</th>
                                            <th>Area</th>
                                            <th>City</th>
                                            <th>Branch</th>
                                            <th>Visit Purpose</th>
                                            <th>Reguler Member Code</th>
                                            <th>Reguler Member Name</th>
                                            <th>Loyalty Member Code</th>
                                            <th>Loyalty Member Name</th>
                                            <th>Loyalty Member Type</th>
                                            <th>Employee Code</th>
                                            <th>Employee Name</th>
                                            <th>Ext. Employee Code</th>
                                            <th>Ext. Employee Name</th>
                                            <th>Payment Method</th>
                                            <th>Parent Payment Method</th>
                                            <th>Trace Number</th>
                                            <th>Approval Code</th>
                                            <th>EDC Terminal ID</th>
                                            <th>Bank Name</th>
                                            <th>Card Number</th>
                                            <th>Additional Info</th>
                                            <th>Notes</th>
                                            <th>MDR</th>
                                            <th>Payment Amount</th>
                                            <th>Nett After MDR</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($transactions as $i => $t)
                                            <tr>
                                                <td>{{ $transactions->firstItem() + $i }}</td>
                                                <td>{{ $t->sales_number }}</td>
                                                <td>{{ $t->bill_number }}</td>
                                                <td>{{ $t->sales_date_in ? $t->sales_date_in->format('Y-m-d H:i:s') : '' }}</td>
                                                <td>{{ $t->sales_date_out ? $t->sales_date_out->format('Y-m-d H:i:s') : '' }}</td>
                                                <td>{{ $t->brand }}</td>
                                                <td>{{ $t->area }}</td>
                                                <td>{{ $t->city }}</td>
                                                <td>{{ $t->branch }}</td>
                                                <td>{{ $t->visit_purpose }}</td>
                                                <td>{{ $t->reguler_member_code }}</td>
                                                <td>{{ $t->reguler_member_name }}</td>
                                                <td>{{ $t->loyalty_member_code }}</td>
                                                <td>{{ $t->loyalty_member_name }}</td>
                                                <td>{{ $t->loyalty_member_type }}</td>
                                                <td>{{ $t->employee_code }}</td>
                                                <td>{{ $t->employee_name }}</td>
                                                <td>{{ $t->external_employee_code }}</td>
                                                <td>{{ $t->external_employee_name }}</td>
                                                <td>{{ $t->payment_method }}</td>
                                                <td>{{ $t->parent_payment_method }}</td>
                                                <td>{{ $t->trace_number }}</td>
                                                <td>{{ $t->approval_code }}</td>
                                                <td>{{ $t->edc_terminal_id }}</td>
                                                <td>{{ $t->bank_name }}</td>
                                                <td>{{ $t->card_number }}</td>
                                                <td>{{ $t->additional_info }}</td>
                                                <td>{{ $t->notes }}</td>
                                                <td class="text-right">{{ number_format($t->mdr, 2) }}</td>
                                                <td class="text-right">{{ number_format($t->payment_amount, 2) }}</td>
                                                <td class="text-right">{{ number_format($t->nett_after_mdr, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="31" class="text-center py-4">
                                                    No transactions found.
                                                    <a href="{{ route('transactions.import') }}" class="btn btn-sm btn-outline-primary ml-2">
                                                        Import Dataset.xlsx
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="px-3 py-2">
                                {{ $transactions->withQueryString()->links('pagination::bootstrap-4') }}
                            </div>
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
