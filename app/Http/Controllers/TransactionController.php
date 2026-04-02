<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Imports\TransactionsImport;
use App\Exports\TransactionsExport;
use App\Services\ExcelSyncService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Transaction::query();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('sales_number', 'LIKE', "%{$search}%")
                  ->orWhere('brand', 'LIKE', "%{$search}%")
                  ->orWhere('payment_method', 'LIKE', "%{$search}%");
        }
        
        $transactions = $query->orderBy('id', 'asc')->paginate(15);
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        return view('transactions.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'sales_number'           => 'required|unique:transactions,sales_number',
            'bill_number'            => 'nullable|string|max:255',
            'sales_date_in'          => 'required|date',
            'sales_date_out'         => 'nullable|date',
            'brand'                  => 'required|string|max:255',
            'area'                   => 'nullable|string|max:255',
            'city'                   => 'nullable|string|max:255',
            'branch'                 => 'nullable|string|max:255',
            'visit_purpose'          => 'nullable|string|max:255',
            'reguler_member_code'    => 'nullable|string|max:255',
            'reguler_member_name'    => 'nullable|string|max:255',
            'loyalty_member_code'    => 'nullable|string|max:255',
            'loyalty_member_name'    => 'nullable|string|max:255',
            'loyalty_member_type'    => 'nullable|string|max:255',
            'employee_code'          => 'nullable|string|max:255',
            'employee_name'          => 'nullable|string|max:255',
            'external_employee_code' => 'nullable|string|max:255',
            'external_employee_name' => 'nullable|string|max:255',
            'payment_method'         => 'required|string|max:255',
            'parent_payment_method'  => 'nullable|string|max:255',
            'trace_number'           => 'nullable|string|max:255',
            'approval_code'          => 'nullable|string|max:255',
            'edc_terminal_id'        => 'nullable|string|max:255',
            'bank_name'              => 'nullable|string|max:255',
            'card_number'            => 'nullable|string|max:255',
            'additional_info'        => 'nullable|string',
            'notes'                  => 'nullable|string',
            'mdr'                    => 'nullable|numeric',
            'payment_amount'         => 'required|numeric',
            'nett_after_mdr'         => 'nullable|numeric',
        ]);

        // Ensure numeric columns are never null (DB columns have NOT NULL constraint)
        $validatedData['mdr']           = $validatedData['mdr'] ?? 0;
        $validatedData['payment_amount'] = $validatedData['payment_amount'] ?? 0;
        // Auto-calculate nett_after_mdr if not provided
        $validatedData['nett_after_mdr'] = $validatedData['nett_after_mdr']
            ?? ($validatedData['payment_amount'] - $validatedData['mdr']);

        Transaction::create($validatedData);

        return redirect()->route('transactions.index')->with('success', 'Transaction added successfully!');
    }

    public function import()
    {
        set_time_limit(0);

        try {
            // Disable observer during bulk import (data comes FROM the Excel file,
            // no need to write it back — would be circular and slow)
            Transaction::withoutEvents(function () {
                Transaction::truncate();
                Excel::import(new TransactionsImport, public_path('Dataset.xlsx'));
            });

            return redirect()->route('transactions.index')
                ->with('success', 'Dataset.xlsx imported successfully! ' . Transaction::count() . ' records loaded.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Friendly message for duplicate key or SQL errors
            return redirect()->route('transactions.index')
                ->with('error', 'Import failed: A duplicate Sales Number was detected. Please clear the data first or check your dataset for duplicates.');
        } catch (\Exception $e) {
            return redirect()->route('transactions.index')
                ->with('error', 'Import error: ' . class_basename($e) . ' — ' . substr($e->getMessage(), 0, 150));
        }
    }

    public function export()
    {
        $result = ExcelSyncService::syncAllTransactions();

        if ($result['success']) {
            return redirect()->route('transactions.index')
                ->with('success', $result['message']);
        }

        return redirect()->route('transactions.index')
            ->with('error', $result['message']);
    }
}

