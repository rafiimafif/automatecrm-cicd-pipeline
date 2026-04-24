<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $stages = DealStage::orderBy('order')
            ->with(['deals' => function ($q) use ($request) {
                $q->where('status', 'open')->with(['customer', 'assignee']);

                if ($request->filled('search')) {
                    $q->where('title', 'like', '%'.$request->search.'%');
                }
            }])
            ->get();

        $customers = Customer::orderBy('fname')->get();
        $tags = Tag::orderBy('name')->get();

        // Stats
        $totalOpen = Deal::where('status', 'open')->count();
        $totalValue = Deal::where('status', 'open')->sum('value');
        $wonThisMonth = Deal::where('status', 'won')
            ->where('updated_at', '>=', now()->startOfMonth())
            ->count();
        $lostThisMonth = Deal::where('status', 'lost')
            ->where('updated_at', '>=', now()->startOfMonth())
            ->count();

        return view('deals.index', compact(
            'stages',
            'customers',
            'tags',
            'totalOpen',
            'totalValue',
            'wonThisMonth',
            'lostThisMonth'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'deal_stage_id' => 'required|exists:deal_stages,id',
            'value' => 'required|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['assigned_to'] = Auth::id();
        $validated['status'] = 'open';

        $deal = Deal::create($validated);

        return redirect()->route('deals.index')->with('success', 'Deal created: '.$deal->title);
    }

    public function show(Deal $deal)
    {
        $deal->load(['customer', 'stage', 'assignee', 'tags', 'notes.user', 'tasks']);
        $stages = DealStage::orderBy('order')->get();
        $tags = Tag::orderBy('name')->get();

        return view('deals.show', compact('deal', 'stages', 'tags'));
    }

    public function update(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'deal_stage_id' => 'sometimes|exists:deal_stages,id',
            'value' => 'sometimes|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'status' => 'sometimes|in:open,won,lost',
            'notes' => 'nullable|string|max:2000',
        ]);

        $deal->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'deal' => $deal->fresh()->load('stage')]);
        }

        return back()->with('success', 'Deal updated.');
    }

    public function updateStage(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'deal_stage_id' => 'required|exists:deal_stages,id',
        ]);

        $deal->update($validated);

        return response()->json(['success' => true]);
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();

        return redirect()->route('deals.index')->with('success', 'Deal deleted.');
    }
}
