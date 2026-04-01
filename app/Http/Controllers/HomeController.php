<?php

namespace App\Http\Controllers;

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
        $transactions = Transaction::orderBy('sales_date_in', 'desc')->take(10)->get();
        $paymentMethods = Transaction::select('payment_method', DB::raw('count(*) as total'))
            ->groupBy('payment_method')
            ->get();

        return view('welcome', compact('transactions', 'paymentMethods'));
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
