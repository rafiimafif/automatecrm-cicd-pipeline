<!DOCTYPE html>
<html lang="en">
<head>
    @extends('layouts.head')
    <style>
        .priority-urgent { border-left: 4px solid #e74a3b !important; }
        .priority-high { border-left: 4px solid #f6c23e !important; }
        .priority-medium { border-left: 4px solid #4e73df !important; }
        .priority-low { border-left: 4px solid #858e96 !important; }
        .task-overdue { background-color: #fff5f5 !important; }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <x-sidebar></x-sidebar>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <x-topbar></x-topbar>
                <div class="container-fluid">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif

                    <!-- Header -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Tasks</h1>
                        <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#newTaskModal">
                            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i>New Task
                        </button>
                    </div>

                    <!-- Stats -->
                    <div class="row mb-4">
                        <div class="col-xl-4 col-md-4 mb-3">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Overdue</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overdueTasks }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-4 mb-3">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pending</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingTasks }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-4 mb-3">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed Today</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $completedToday }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card shadow mb-4">
                        <div class="card-body py-3">
                            <form method="GET" action="{{ route('tasks.index') }}" class="row align-items-end g-2">
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <select class="form-control" name="status">
                                        <option value="">Active (Pending + In Progress)</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <select class="form-control" name="priority">
                                        <option value="">All Priorities</option>
                                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>🟡 High</option>
                                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>🔵 Medium</option>
                                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>⚪ Low</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <input type="text" class="form-control" name="search" placeholder="Search tasks..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2 mb-2 mb-md-0">
                                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter mr-1"></i>Filter</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Task List -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            @forelse ($tasks as $task)
                                <div class="d-flex align-items-start p-3 mb-2 border rounded priority-{{ $task->priority }} {{ $task->isOverdue() ? 'task-overdue' : '' }}">
                                    <!-- Status Toggle -->
                                    <form action="{{ route('tasks.update', $task->id) }}" method="POST" class="mr-3">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="{{ $task->status == 'completed' ? 'pending' : 'completed' }}">
                                        <button type="submit" class="btn btn-sm {{ $task->status == 'completed' ? 'btn-success' : 'btn-outline-secondary' }}" style="width:32px;height:32px;">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>

                                    <!-- Task Info -->
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 {{ $task->status == 'completed' ? 'text-decoration-line-through text-muted' : '' }}">
                                                    {{ $task->title }}
                                                </h6>
                                                @if($task->description)
                                                    <p class="text-muted small mb-1">{{ Str::limit($task->description, 100) }}</p>
                                                @endif
                                                <div class="small">
                                                    @switch($task->priority)
                                                        @case('urgent') <span class="badge badge-danger">Urgent</span> @break
                                                        @case('high') <span class="badge badge-warning">High</span> @break
                                                        @case('medium') <span class="badge badge-primary">Medium</span> @break
                                                        @case('low') <span class="badge badge-secondary">Low</span> @break
                                                    @endswitch

                                                    @if($task->due_date)
                                                        <span class="ml-2 {{ $task->isOverdue() ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                                            <i class="fas fa-calendar-alt mr-1"></i>{{ $task->due_date->format('M d, Y') }}
                                                            @if($task->isOverdue()) (overdue) @endif
                                                        </span>
                                                    @endif

                                                    @if($task->taskable)
                                                        <span class="ml-2 text-muted">
                                                            @if($task->taskable_type == 'App\\Models\\Customer')
                                                                <i class="fas fa-user mr-1"></i>{{ $task->taskable->fname ?? '' }} {{ $task->taskable->lname ?? '' }}
                                                            @elseif($task->taskable_type == 'App\\Models\\Deal')
                                                                <i class="fas fa-handshake mr-1"></i>{{ $task->taskable->title ?? '' }}
                                                            @endif
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Delete?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger btn-sm"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-gray-500">
                                    <i class="fas fa-tasks fa-3x mb-3 text-gray-300"></i>
                                    <p>No tasks found. Create your first task!</p>
                                </div>
                            @endforelse

                            <div class="d-flex justify-content-center mt-3">
                                {{ $tasks->links() }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <x-footer></x-footer>
        </div>
    </div>

    <!-- New Task Modal -->
    <div class="modal fade" id="newTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Task</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="{{ route('tasks.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" class="form-control" name="title" required placeholder="e.g., Follow up with client">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Optional details..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Due Date</label>
                                    <input type="date" class="form-control" name="due_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Priority</label>
                                    <select class="form-control" name="priority" required>
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Create Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-main_scripts></x-main_scripts>
</body>
</html>
