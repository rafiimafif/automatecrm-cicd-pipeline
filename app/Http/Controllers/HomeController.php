<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\ServicetoCustomer;
use App\Models\Task;
use App\Models\Transaction;
use Auth;
use Illuminate\Support\Facades\DB;
use Session;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('auth.login');
    }

    public function dashboard()
    {
        // Basic financials
        $totalPayment = Transaction::sum('payment_amount');
        $totalMdr = Transaction::sum('mdr');
        $totalNett = Transaction::sum('nett_after_mdr');
        $transactionCount = Transaction::count();
        $avgTransaction = $transactionCount > 0 ? $totalPayment / $transactionCount : 0;

        // Top brand
        $topBrand = Transaction::select('brand', DB::raw('count(*) as total'))
            ->groupBy('brand')
            ->orderByDesc('total')
            ->first();

        // Payment methods breakdown
        $paymentMethods = Transaction::select('payment_method', DB::raw('count(*) as total'), DB::raw('SUM(payment_amount) as amount'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // Top payment methods by amount
        $topPaymentByAmount = Transaction::select('payment_method', DB::raw('SUM(payment_amount) as total_amount'))
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->take(5)
            ->get();

        // Brands breakdown
        $brandBreakdown = Transaction::select('brand', DB::raw('count(*) as total'), DB::raw('SUM(payment_amount) as amount'))
            ->groupBy('brand')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        // Recent transactions
        $transactions = Transaction::orderBy('sales_date_in', 'desc')->take(15)->get();

        // Daily transaction trend (last 30 days)
        $dailyTrend = Transaction::select(
            DB::raw('DATE(sales_date_in) as date'),
            DB::raw('count(*) as count'),
            DB::raw('SUM(payment_amount) as amount')
        )
            ->where('sales_date_in', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(sales_date_in)'))
            ->orderBy('date')
            ->get();

        // City breakdown
        $cityBreakdown = Transaction::select('city', DB::raw('count(*) as total'), DB::raw('SUM(payment_amount) as amount'))
            ->groupBy('city')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // ── CRM KPI Metrics ──

        // Customer metrics
        $totalCustomers = Customer::count();
        $newCustomersThisMonth = Customer::where('created_at', '>=', now()->startOfMonth())->count();

        // Deal pipeline metrics
        $openDeals = Deal::where('status', 'open')->count();
        $openDealsValue = Deal::where('status', 'open')->sum('value');
        $wonDealsThisMonth = Deal::where('status', 'won')
            ->where('updated_at', '>=', now()->startOfMonth())->count();
        $wonDealsValueThisMonth = Deal::where('status', 'won')
            ->where('updated_at', '>=', now()->startOfMonth())->sum('value');

        // Pipeline stages for mini funnel
        $pipelineStages = DealStage::withCount(['deals' => function ($q) {
            $q->where('status', 'open');
        }])
            ->withSum(['deals' => function ($q) {
                $q->where('status', 'open');
            }], 'value')
            ->orderBy('order')
            ->get();

        // Task metrics
        $overdueTasks = Task::overdue()->count();
        $pendingTasks = Task::pending()->count();

        // Upcoming renewals (services expiring in next 30 days)
        $upcomingRenewals = ServicetoCustomer::where('expiration', '>=', now())
            ->where('expiration', '<=', now()->addDays(30))
            ->count();

        // Recent activity
        $recentActivity = DB::table('activity_logs')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('welcome', compact(
            'transactions', 'paymentMethods', 'topBrand', 'totalPayment', 'totalMdr', 'totalNett', 'transactionCount', 'avgTransaction',
            'topPaymentByAmount', 'brandBreakdown', 'dailyTrend', 'cityBreakdown',
            // CRM KPIs
            'totalCustomers', 'newCustomersThisMonth',
            'openDeals', 'openDealsValue', 'wonDealsThisMonth', 'wonDealsValueThisMonth',
            'pipelineStages', 'overdueTasks', 'pendingTasks', 'upcomingRenewals', 'recentActivity'
        ));
    }

    public static function finance()
    {
        $totalPayment = Transaction::sum('payment_amount');
        $totalMdr = Transaction::sum('mdr');
        $totalNett = Transaction::sum('nett_after_mdr');
        $transactionCount = Transaction::count();

        return [$totalPayment, $totalMdr, $totalNett, $transactionCount];
    }

    public function logout()
    {
        Session::flush();
        Auth::logout();

        return redirect('login');
    }
}
