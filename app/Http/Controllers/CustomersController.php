<?php

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use App\Imports\CustomersImport;
use App\Mail\SendMail;
use App\Models\Customer;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mail;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with('tags');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                    ->orWhere('lname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by tag
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->tag);
            });
        }

        $customers = $query->orderBy('fname')->paginate(25)->withQueryString();
        $tags = Tag::orderBy('name')->get();
        $allTags = $tags;

        return view('customers.index', compact('customers', 'tags', 'allTags'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $fname = $request->input('fname');
        $lname = $request->input('lname');
        $company = $request->input('company');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $address = $request->input('address');

        $insert = DB::table('customers')->insertGetId(['fname' => $fname, 'lname' => $lname, 'email' => $email, 'phone' => $phone, 'address' => $address, 'company' => $company]);

        return redirect('/customers')->with('success', 'New Customer Added');
    }

    public function show(Customer $customers)
    {
        //
    }

    public function edit($id)
    {

        $cus = Customer::where('id', $id)->firstOrFail();

        $servicetocustomer = DB::table('servicetocustomer')
            ->join('services', 'services.id', '=', 'servicetocustomer.service_id')
            ->leftJoin('payments', 'payments.id', '=', 'servicetocustomer.payment_id')
            ->where('servicetocustomer.customer_id', '=', $id)
            ->select('servicetocustomer.*', 'services.name as service_name', 'payments.id as payment_id')
            ->get();

        $services = DB::table('servicetocustomer')
            ->join('services', 'services.id', '=', 'servicetocustomer.service_id')
            ->leftJoin('payments', 'payments.id', '=', 'servicetocustomer.payment_id')
            ->where('servicetocustomer.customer_id', '=', $id)
            ->select(
                'servicetocustomer.*',
                'services.name as service_name',
                'payments.id as payment_id',
                'payments.price as payment_amount',
                'payments.payment_date',
                'payments.payment_type',
                'payments.notes as payment_notes'
            )
            ->get();

        $payments = DB::table('payments')
            ->join('servicetocustomer', 'servicetocustomer.payment_id', '=', 'payments.id')
            ->join('services', 'services.id', '=', 'servicetocustomer.service_id')
            ->where('servicetocustomer.customer_id', '=', $id)
            ->select('payments.*', 'services.name as servname')
            ->get();

        $unpaid_payments = DB::table('servicetocustomer')
            ->join('services', 'services.id', '=', 'servicetocustomer.service_id')
            ->where('servicetocustomer.customer_id', '=', $id)
            ->whereNull('servicetocustomer.payment_id')
            ->select('servicetocustomer.*', 'services.name as servname')
            ->get();

        // Load notes for the customer timeline
        $notes = $cus->notes()->with('user')->orderBy('created_at', 'desc')->get();

        // Load tags
        $customerTags = $cus->tags;
        $allTags = Tag::orderBy('name')->get();

        return view('customers.single', compact('cus', 'services', 'payments', 'unpaid_payments', 'servicetocustomer', 'notes', 'customerTags', 'allTags'));
    }

    public function sendmessage(Request $request)
    {
        $data = [
            'email' => $request->email,
            'your_message' => $request->your_message,
        ];

        Mail::to($request->email)->send(new SendMail($data));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'required|email|unique:customers,email,'.$customer->id,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        $customer->update($validated);

        return redirect()->route('customer_edit', ['id' => $customer->id])
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect('/customers')->with('success', 'Customer deleted successfully.');
    }

    public function showTools()
    {
        return view('tools');
    }

    public function exportCustomers()
    {
        return Excel::download(new CustomersExport, 'customers.xlsx');
    }

    public function importCustomers(Request $request)
    {
        Excel::import(new CustomersImport, $request->file('file'));

        return redirect('/')->with('success', 'All good!');
    }
}
