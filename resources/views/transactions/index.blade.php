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

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Transaction List</h6>
                            <div class="d-flex">
                                <a href="{{ route('transactions.create') }}" class="btn btn-success btn-sm mr-2"><i class="fas fa-plus"></i> Add Transaction</a>
                                <a href="{{ route('transactions.export') }}" class="btn btn-warning btn-sm mr-3"><i class="fas fa-file-excel"></i> Export Excel</a>
                                <form action="{{ route('transactions.index') }}" method="GET" class="form-inline">
                                    <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search Sales # or Brand" value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Search</button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" width="100%" cellspacing="0" style="white-space: nowrap;">
                                    <thead>
                                        <tr>
                                            <th>Sales Number</th>
                                            <th>Date In</th>
                                            <th>Brand</th>
                                            <th>City</th>
                                            <th>Visit Purpose</th>
                                            <th>Payment Method</th>
                                            <th>Bank</th>
                                            <th>MDR</th>
                                            <th>Amount</th>
                                            <th>Nett</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($transactions as $t)
                                            <tr>
                                                <td>{{ $t->sales_number }}</td>
                                                <td>{{ $t->sales_date_in ? $t->sales_date_in->format('Y-m-d H:i') : '' }}</td>
                                                <td>{{ $t->brand }}</td>
                                                <td>{{ $t->city }}</td>
                                                <td>{{ $t->visit_purpose }}</td>
                                                <td>{{ $t->payment_method }}</td>
                                                <td>{{ $t->bank_name }}</td>
                                                <td>{{ number_format($t->mdr, 2) }}</td>
                                                <td>{{ number_format($t->payment_amount, 2) }}</td>
                                                <td>{{ number_format($t->nett_after_mdr, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center">No transactions found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- Pagination -->
                            <div class="mt-3">
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
