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
                        <div class="btn-group" role="group">
                            <a href="{{ route('customers.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-outline-primary shadow-sm"><i class="fas fa-users fa-sm mr-1"></i>Customers</a>
                            <a href="{{ route('deals.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-outline-info shadow-sm"><i class="fas fa-handshake fa-sm mr-1"></i>Deals</a>
                            <a href="{{ route('tasks.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-outline-warning shadow-sm"><i class="fas fa-tasks fa-sm mr-1"></i>Tasks</a>
                            <a href="{{ route('transactions.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Add Transaction</a>
                            <a href="{{ route('transactions.import') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-file-excel fa-sm text-white-50"></i> Re-Import Dataset</a>
                        </div>
                    </div>
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif

                    <!-- KPI Row 1: Financial Metrics -->
                    <div class="row mb-4">
                        <!-- Total Payment Amount -->
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Payment Amount</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalPayment, 2) }}</div>
                                            <div class="text-xs text-gray-500 mt-1">{{ $transactionCount }} transactions</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-money-bill fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nett After MDR -->
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Nett After MDR</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalNett, 2) }}</div>
                                            <div class="text-xs text-gray-500 mt-1" id="nettPercent">0%</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total MDR Fees -->
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total MDR Fees</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalMdr, 2) }}</div>
                                            <div class="text-xs text-gray-500 mt-1" id="mdrPercent">0%</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Avg Transaction Value -->
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg Transaction</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($avgTransaction, 2) }}</div>
                                            <div class="text-xs text-gray-500 mt-1">{{ $transactionCount }} total</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI Row 2: Operational Metrics -->
                    <div class="row mb-4">
                        <!-- Transactions Count -->
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-secondary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total Transactions</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($transactionCount) }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Brand -->
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Top Brand</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $topBrand->brand ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500 mt-1">{{ $topBrand->total ?? 0 }} transactions</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-crown fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods Count -->
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-dark shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Payment Methods</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $paymentMethods->count() }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cities Count -->
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Cities Covered</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $cityBreakdown->count() }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KPI Row 3: CRM Metrics -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Customers</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalCustomers) }}</div>
                                            <div class="text-xs text-gray-500 mt-1">+{{ $newCustomersThisMonth }} this month</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Open Deals</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $openDeals }}</div>
                                            <div class="text-xs text-gray-500 mt-1">Rp {{ number_format($openDealsValue, 0) }} pipeline</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-handshake fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Overdue Tasks</div>
                                            <div class="h5 mb-0 font-weight-bold {{ $overdueTasks > 0 ? 'text-danger' : 'text-gray-800' }}">{{ $overdueTasks }}</div>
                                            <div class="text-xs text-gray-500 mt-1">{{ $pendingTasks }} pending</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-tasks fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Expiring Soon</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $upcomingRenewals }}</div>
                                            <div class="text-xs text-gray-500 mt-1">services in 30 days</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pipeline Mini Funnel + Activity Feed -->
                    <div class="row mb-4">
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Pipeline Stages</h6>
                                    <a href="{{ route('deals.index') }}" class="btn btn-sm btn-link">View Pipeline →</a>
                                </div>
                                <div class="card-body">
                                    @foreach ($pipelineStages as $stage)
                                        @if($stage->name !== 'Closed Lost')
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="d-flex align-items-center">
                                                <span class="badge mr-2" style="background-color: {{ $stage->color }}; color: #fff; width: 10px; height: 10px; border-radius: 50%; padding: 0;">&nbsp;</span>
                                                <span>{{ $stage->name }}</span>
                                            </div>
                                            <div>
                                                <span class="badge badge-light mr-1">{{ $stage->deals_count }} deals</span>
                                                <span class="text-muted small">Rp {{ number_format($stage->deals_sum_value ?? 0, 0) }}</span>
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                                    <a href="{{ route('activity.log') }}" class="btn btn-sm btn-link">View All →</a>
                                </div>
                                <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                                    @forelse ($recentActivity as $activity)
                                        <div class="d-flex align-items-start mb-2 pb-2 border-bottom">
                                            <i class="fas fa-circle fa-xs text-primary mr-2 mt-1"></i>
                                            <div>
                                                <small class="font-weight-bold">{{ $activity->action }}</small>
                                                <small class="text-muted d-block">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-center text-gray-500 mb-0">No recent activity</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <!-- Transaction Trend Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Transaction Trend (Last 30 Days)</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="transactionTrendChart" height="80"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods Pie -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Payment Methods</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="paymentMethodChart" height="130"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics Row -->
                    <div class="row mb-4">
                        <!-- Top Brands -->
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Top Brands</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Brand</th>
                                                    <th>Count</th>
                                                    <th>Amount</th>
                                                    <th>%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($brandBreakdown as $brand)
                                                <tr>
                                                    <td><strong>{{ $brand->brand ?: 'Unknown' }}</strong></td>
                                                    <td><span class="badge badge-primary">{{ $brand->total }}</span></td>
                                                    <td>Rp {{ number_format($brand->amount, 0) }}</td>
                                                    <td>{{ round(($brand->amount / $totalPayment) * 100, 1) }}%</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Cities -->
                        <div class="col-xl-6 col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Top Cities</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>City</th>
                                                    <th>Transactions</th>
                                                    <th>Total Amount</th>
                                                    <th>%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($cityBreakdown as $city)
                                                <tr>
                                                    <td><strong>{{ $city->city ?: 'Unknown' }}</strong></td>
                                                    <td><span class="badge badge-info">{{ $city->total }}</span></td>
                                                    <td>Rp {{ number_format($city->amount, 0) }}</td>
                                                    <td>{{ round(($city->amount / $totalPayment) * 100, 1) }}%</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Tables Row -->
                    <div class="row">
                        <!-- Recent Transactions -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
                                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-link">View All →</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Sales #</th>
                                                    <th>Brand</th>
                                                    <th>Date</th>
                                                    <th>Method</th>
                                                    <th>Amount</th>
                                                    <th>Nett</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($transactions as $t)
                                                <tr>
                                                    <td><strong>{{ $t->sales_number }}</strong></td>
                                                    <td>{{ $t->brand }}</td>
                                                    <td><small>{{ $t->sales_date_in ? $t->sales_date_in->format('m-d H:i') : '-' }}</small></td>
                                                    <td><span class="badge badge-secondary">{{ $t->payment_method }}</span></td>
                                                    <td>Rp {{ number_format($t->payment_amount, 0) }}</td>
                                                    <td><strong>Rp {{ number_format($t->nett_after_mdr, 0) }}</strong></td>
                                                </tr>
                                            @endforeach
                                            @if($transactions->isEmpty())
                                                <tr><td colspan="6" class="text-center text-gray-500 py-3">No transactions available</td></tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods by Amount -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Top Payment Methods</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Method</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($topPaymentByAmount as $method)
                                                <tr>
                                                    <td><strong>{{ $method->payment_method ?: 'Unknown' }}</strong></td>
                                                    <td>
                                                        <small>Rp {{ number_format($method->total_amount, 0) }}</small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if($topPaymentByAmount->isEmpty())
                                                <tr><td colspan="2" class="text-center text-gray-500">No data</td></tr>
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

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <script>
        // Calculate percentages
        const totalPayment = {{ $totalPayment }};
        const totalMdr = {{ $totalMdr }};
        const totalNett = {{ $totalNett }};
        
        document.getElementById('mdrPercent').textContent = ((totalMdr / totalPayment) * 100).toFixed(1) + '%';
        document.getElementById('nettPercent').textContent = ((totalNett / totalPayment) * 100).toFixed(1) + '%';

        // Transaction Trend Chart
        const trendCtx = document.getElementById('transactionTrendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($dailyTrend as $trend)
                        '{{ \Carbon\Carbon::parse($trend->date)->format("m-d") }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Transaction Count',
                    data: [
                        @foreach($dailyTrend as $trend)
                            {{ $trend->count }},
                        @endforeach
                    ],
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#4e73df',
                }, {
                    label: 'Amount (Rp 100M)',
                    data: [
                        @foreach($dailyTrend as $trend)
                            {{ $trend->amount / 100000000 }},
                        @endforeach
                    ],
                    borderColor: '#858e96',
                    backgroundColor: 'rgba(133, 142, 150, 0.05)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#858e96',
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Count' } },
                    y1: { type: 'linear', position: 'right', beginAtZero: true, title: { display: true, text: 'Amount (Rp 100M)' }, grid: { drawOnChartArea: false } }
                },
                plugins: { legend: { display: true } }
            }
        });

        // Payment Methods Pie Chart
        const paymentCtx = document.getElementById('paymentMethodChart').getContext('2d');
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    @foreach($paymentMethods as $method)
                        '{{ $method->payment_method ?: "Unknown" }}',
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach($paymentMethods as $method)
                            {{ $method->total }},
                        @endforeach
                    ],
                    backgroundColor: ['#4e73df', '#858e96', '#1cc88a', '#36b9cc', '#f6c23e', '#e74c3c', '#95a5a6'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>

</body>
</html>
