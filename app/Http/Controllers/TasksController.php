<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TasksController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Task::with(['taskable', 'assignee']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->pending();
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tasks = $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy('due_date')
            ->paginate(20);

        $overdueTasks = Task::overdue()->count();
        $pendingTasks = Task::pending()->count();
        $completedToday = Task::where('status', 'completed')
            ->where('updated_at', '>=', now()->startOfDay())
            ->count();

        return view('tasks.index', compact('tasks', 'overdueTasks', 'pendingTasks', 'completedToday'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'taskable_type' => 'nullable|string|in:App\\Models\\Customer,App\\Models\\Deal',
            'taskable_id' => 'nullable|integer',
        ]);

        $validated['assigned_to'] = Auth::id();
        $validated['status'] = 'pending';

        Task::create($validated);

        return back()->with('success', 'Task created.');
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|date',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:pending,in_progress,completed',
        ]);

        $task->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return back()->with('success', 'Task deleted.');
    }
}
