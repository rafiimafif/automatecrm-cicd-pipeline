<!DOCTYPE html>
<html lang="en">

<head>
    @extends('layouts.head')
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <x-sidebar></x-sidebar>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <x-topbar></x-topbar>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Sales Dashboard</h1>
                        <a href="{{ route('transactions.import') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                class="fas fa-file-excel fa-sm text-white-50"></i> Re-Import Dataset.xlsx</a>
                    </div>
                    
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Earnings Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                @php
                                                    $f = App\Http\Controllers\HomeController::finance();
                                                @endphp
                                                Total Payment Amount</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($f[0], 2) }}
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-money-bill fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nett After MDR Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Nett After MDR</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($f[2], 2) }}
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total MDR Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Total MDR Fees
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($f[1], 2) }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Count Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Transactions Count</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($f[3]) }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Recent Transactions -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Sales #</th>
                                                    <th>Brand</th>
                                                    <th>Date In</th>
                                                    <th>Method</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($transactions as $t)
                                                <tr>
                                                    <td>{{ $t->sales_number }}</td>
                                                    <td>{{ $t->brand }}</td>
                                                    <td>{{ $t->sales_date_in ? $t->sales_date_in->format('Y-m-d H:i') : '-' }}</td>
                                                    <td>{{ $t->payment_method }}</td>
                                                    <td>{{ number_format($t->payment_amount, 2) }}</td>
                                                </tr>
                                            @endforeach
                                            @if($transactions->isEmpty())
                                                <tr><td colspan="5" class="text-center">No transactions available. Click Re-Import Dataset above.</td></tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods summary -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Payment Methods</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Method</th>
                                                    <th>Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($paymentMethods as $method)
                                                <tr>
                                                    <td>{{ $method->payment_method ?: 'Unknown' }}</td>
                                                    <td>{{ $method->total }}</td>
                                                </tr>
                                            @endforeach
                                            @if($paymentMethods->isEmpty())
                                                <tr><td colspan="2" class="text-center">No data</td></tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <x-footer></x-footer>

        </div>
    </div>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="{{ route('logout.custom') }}">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <x-main_scripts></x-main_scripts>

</body>
</html>
