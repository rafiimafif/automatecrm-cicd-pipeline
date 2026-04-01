<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Imports\TransactionsImport;
use App\Exports\TransactionsExport;
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
        
        $transactions = $query->orderBy('sales_date_in', 'desc')->paginate(15);
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        return view('transactions.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'sales_number'   => 'required|unique:transactions,sales_number',
            'brand'          => 'required|string|max:255',
            'sales_date_in'  => 'required|date',
            'city'           => 'nullable|string|max:255',
            'visit_purpose'  => 'nullable|string|max:255',
            'payment_method' => 'required|string|max:255',
            'bank_name'      => 'nullable|string|max:255',
            'payment_amount' => 'required|numeric',
            'mdr'            => 'nullable|numeric',
            'nett_after_mdr' => 'nullable|numeric',
        ]);

        Transaction::create($validatedData);

        return redirect()->route('transactions.index')->with('success', 'Manual Transaction Added Successfully!');
    }

    public function import()
    {
        set_time_limit(0);

        try {
            // Remove existing to prevent duplicates on fresh import
            Transaction::truncate();

            // Trigger import from the Dataset.xlsx located in public folder
            Excel::import(new TransactionsImport, public_path('Dataset.xlsx'));

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
        // Raise memory limit temporarily for this large spreadsheet operation
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $filePath = public_path('Dataset.xlsx');

        try {
            // Load existing Dataset.xlsx (read-data-only saves ~50% RAM)
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false); // need write access

            $spreadsheet = $reader->load($filePath);
            $sheet       = $spreadsheet->getActiveSheet();

            // Collect Sales Numbers already in the file (column A, starting row 2)
            $highestRow   = $sheet->getHighestRow();
            $existingKeys = [];
            for ($row = 2; $row <= $highestRow; $row++) {
                $val = $sheet->getCell("A{$row}")->getValue();
                if ($val) {
                    $existingKeys[$val] = true;
                }
            }

            // Get all DB transactions NOT already in the file
            $newTransactions = Transaction::orderBy('sales_date_in', 'asc')
                ->get()
                ->filter(function ($t) use ($existingKeys) {
                    return !isset($existingKeys[$t->sales_number]);
                });

            // Append each new transaction as a row
            $nextRow  = $highestRow + 1;
            $newCount = 0;
            foreach ($newTransactions as $t) {
                $sheet->setCellValue("A{$nextRow}", $t->sales_number);
                $sheet->setCellValue("B{$nextRow}", $t->bill_number);
                $sheet->setCellValue("C{$nextRow}", $t->sales_date_in ? $t->sales_date_in->format('Y-m-d H:i:s') : '');
                $sheet->setCellValue("D{$nextRow}", $t->sales_date_out ? $t->sales_date_out->format('Y-m-d H:i:s') : '');
                $sheet->setCellValue("E{$nextRow}", $t->brand);
                $sheet->setCellValue("F{$nextRow}", $t->area);
                $sheet->setCellValue("G{$nextRow}", $t->city);
                $sheet->setCellValue("H{$nextRow}", $t->branch);
                $sheet->setCellValue("I{$nextRow}", $t->visit_purpose);
                $sheet->setCellValue("J{$nextRow}", $t->reguler_member_code);
                $sheet->setCellValue("K{$nextRow}", $t->reguler_member_name);
                $sheet->setCellValue("L{$nextRow}", $t->loyalty_member_code);
                $sheet->setCellValue("M{$nextRow}", $t->loyalty_member_name);
                $sheet->setCellValue("N{$nextRow}", $t->loyalty_member_type);
                $sheet->setCellValue("O{$nextRow}", $t->employee_code);
                $sheet->setCellValue("P{$nextRow}", $t->employee_name);
                $sheet->setCellValue("Q{$nextRow}", $t->external_employee_code);
                $sheet->setCellValue("R{$nextRow}", $t->external_employee_name);
                $sheet->setCellValue("S{$nextRow}", $t->payment_method);
                $sheet->setCellValue("T{$nextRow}", $t->parent_payment_method);
                $sheet->setCellValue("U{$nextRow}", $t->trace_number);
                $sheet->setCellValue("V{$nextRow}", $t->approval_code);
                $sheet->setCellValue("W{$nextRow}", $t->edc_terminal_id);
                $sheet->setCellValue("X{$nextRow}", $t->bank_name);
                $sheet->setCellValue("Y{$nextRow}", $t->card_number);
                $sheet->setCellValue("Z{$nextRow}", $t->additional_info);
                $sheet->setCellValue("AA{$nextRow}", $t->notes);
                $sheet->setCellValue("AB{$nextRow}", $t->mdr);
                $sheet->setCellValue("AC{$nextRow}", $t->payment_amount);
                $sheet->setCellValue("AD{$nextRow}", $t->nett_after_mdr);
                $nextRow++;
                $newCount++;
            }

            // Save updated file back to public/Dataset.xlsx
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            // Save to a temp path first, then replace (prevents corruption on failure)
            $tempPath = storage_path('app/Dataset_temp.xlsx');
            $writer->save($tempPath);

            // Free memory before file operations
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $writer, $reader);

            // Atomically replace the production file
            copy($tempPath, $filePath);
            @unlink($tempPath);

            // Stream the updated file as a download
            return response()->download($filePath, 'Dataset.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(false);

        } catch (\Exception $e) {
            return redirect()->route('transactions.index')
                ->with('error', 'Export failed: ' . class_basename($e) . ' — ' . substr($e->getMessage(), 0, 200));
        }
    }
}

