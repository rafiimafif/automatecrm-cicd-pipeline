<!DOCTYPE html>
<html lang="en">
<head>
    @extends('layouts.head')
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
                        <div>
                            <a href="{{ route('deals.index') }}" class="text-decoration-none text-gray-500"><i class="fas fa-arrow-left mr-2"></i>Back to Pipeline</a>
                            <h1 class="h3 mb-0 text-gray-800 mt-2">{{ $deal->title }}</h1>
                        </div>
                        <div>
                            <span class="badge px-3 py-2" style="background-color: {{ $deal->stage->color }}; color: #fff; font-size: 0.9rem;">
                                {{ $deal->stage->name }}
                            </span>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Deal Details -->
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Deal Details</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('deals.update', $deal->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="small mb-1">Title</label>
                                                <input type="text" class="form-control" name="title" value="{{ $deal->title }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small mb-1">Value (Rp)</label>
                                                <input type="number" class="form-control" name="value" value="{{ $deal->value }}" step="0.01">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="small mb-1">Stage</label>
                                                <select class="form-control" name="deal_stage_id">
                                                    @foreach ($stages as $stage)
                                                        <option value="{{ $stage->id }}" {{ $deal->deal_stage_id == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1">Status</label>
                                                <select class="form-control" name="status">
                                                    <option value="open" {{ $deal->status == 'open' ? 'selected' : '' }}>Open</option>
                                                    <option value="won" {{ $deal->status == 'won' ? 'selected' : '' }}>Won</option>
                                                    <option value="lost" {{ $deal->status == 'lost' ? 'selected' : '' }}>Lost</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small mb-1">Expected Close</label>
                                                <input type="date" class="form-control" name="expected_close_date"
                                                    value="{{ $deal->expected_close_date?->format('Y-m-d') }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="small mb-1">Notes</label>
                                            <textarea class="form-control" name="notes" rows="3">{{ $deal->notes }}</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save Changes</button>
                                        <form action="{{ route('deals.destroy', $deal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this deal?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash mr-1"></i>Delete</button>
                                        </form>
                                    </form>
                                </div>
                            </div>

                            <!-- Notes Timeline -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-stream mr-1"></i>Notes & Activity</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('notes.store') }}" class="mb-4">
                                        @csrf
                                        <input type="hidden" name="notable_type" value="App\Models\Deal">
                                        <input type="hidden" name="notable_id" value="{{ $deal->id }}">
                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <select class="form-control" name="type">
                                                    <option value="note">📝 Note</option>
                                                    <option value="call">📞 Call</option>
                                                    <option value="email">📧 Email</option>
                                                    <option value="meeting">🤝 Meeting</option>
                                                </select>
                                            </div>
                                            <div class="col-md-7 mb-2">
                                                <input type="text" class="form-control" name="content" placeholder="Add a note..." required>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <button type="submit" class="btn btn-primary btn-block">Add</button>
                                            </div>
                                        </div>
                                    </form>

                                    @forelse ($deal->notes as $note)
                                        <div class="d-flex mb-3 pb-3 border-bottom">
                                            <div class="mr-3">
                                                @switch($note->type)
                                                    @case('call') <span class="badge badge-info" style="font-size:1.1rem;">📞</span> @break
                                                    @case('email') <span class="badge badge-warning" style="font-size:1.1rem;">📧</span> @break
                                                    @case('meeting') <span class="badge badge-success" style="font-size:1.1rem;">🤝</span> @break
                                                    @default <span class="badge badge-secondary" style="font-size:1.1rem;">📝</span>
                                                @endswitch
                                            </div>
                                            <div class="flex-grow-1">
                                                <p class="mb-1">{{ $note->content }}</p>
                                                <small class="text-muted">{{ ucfirst($note->type) }} · {{ $note->created_at->diffForHumans() }} @if($note->user) · by {{ $note->user->name }} @endif</small>
                                            </div>
                                            <div>
                                                <form action="{{ route('notes.destroy', $note->id) }}" method="POST" onsubmit="return confirm('Delete?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger btn-sm p-0"><i class="fas fa-times"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-center text-gray-500">No notes yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar Info -->
                        <div class="col-lg-4">
                            <!-- Customer -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Customer</h6></div>
                                <div class="card-body">
                                    @if($deal->customer)
                                        <h5><a href="{{ route('customer_edit', $deal->customer->id) }}">{{ $deal->customer->fname }} {{ $deal->customer->lname }}</a></h5>
                                        <p class="mb-1"><i class="fas fa-building mr-1"></i>{{ $deal->customer->company ?? 'N/A' }}</p>
                                        <p class="mb-1"><i class="fas fa-envelope mr-1"></i>{{ $deal->customer->email }}</p>
                                        <p class="mb-0"><i class="fas fa-phone mr-1"></i>{{ $deal->customer->phone ?? 'N/A' }}</p>
                                    @else
                                        <p class="text-gray-500 mb-0">No customer linked</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Info</h6></div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr><td class="text-gray-500">Created</td><td>{{ $deal->created_at->format('M d, Y') }}</td></tr>
                                        <tr><td class="text-gray-500">Updated</td><td>{{ $deal->updated_at->diffForHumans() }}</td></tr>
                                        <tr><td class="text-gray-500">Assigned</td><td>{{ $deal->assignee?->name ?? 'Unassigned' }}</td></tr>
                                        <tr><td class="text-gray-500">Value</td><td class="font-weight-bold text-success">Rp {{ number_format($deal->value, 0) }}</td></tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Tags -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Tags</h6></div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        @foreach ($deal->tags as $tag)
                                            <span class="badge mr-1 mb-1" style="background-color: {{ $tag->color }}; color: #fff;">
                                                {{ $tag->name }}
                                                <form action="{{ route('tags.detach') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="tag_id" value="{{ $tag->id }}">
                                                    <input type="hidden" name="taggable_type" value="App\Models\Deal">
                                                    <input type="hidden" name="taggable_id" value="{{ $deal->id }}">
                                                    <button type="submit" class="btn btn-link p-0 text-white" style="font-size:0.7rem;">&times;</button>
                                                </form>
                                            </span>
                                        @endforeach
                                    </div>
                                    <form action="{{ route('tags.attach') }}" method="POST" class="form-inline">
                                        @csrf
                                        <input type="hidden" name="taggable_type" value="App\Models\Deal">
                                        <input type="hidden" name="taggable_id" value="{{ $deal->id }}">
                                        <select class="form-control form-control-sm mr-2" name="tag_id">
                                            @foreach ($tags as $tag)
                                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm">Add</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Tasks -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Tasks</h6></div>
                                <div class="card-body">
                                    @forelse ($deal->tasks as $task)
                                        <div class="d-flex align-items-center mb-2">
                                            <form action="{{ route('tasks.update', $task->id) }}" method="POST" class="mr-2">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="status" value="{{ $task->status == 'completed' ? 'pending' : 'completed' }}">
                                                <button type="submit" class="btn btn-sm {{ $task->status == 'completed' ? 'btn-success' : 'btn-outline-secondary' }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <span class="{{ $task->status == 'completed' ? 'text-decoration-line-through text-muted' : '' }}">{{ $task->title }}</span>
                                        </div>
                                    @empty
                                        <p class="text-gray-500 mb-0">No tasks</p>
                                    @endforelse
                                    <hr>
                                    <form method="POST" action="{{ route('tasks.store') }}" class="form-inline">
                                        @csrf
                                        <input type="hidden" name="taskable_type" value="App\Models\Deal">
                                        <input type="hidden" name="taskable_id" value="{{ $deal->id }}">
                                        <input type="hidden" name="priority" value="medium">
                                        <input type="text" class="form-control form-control-sm mr-2 flex-grow-1" name="title" placeholder="Quick task..." required>
                                        <button type="submit" class="btn btn-primary btn-sm">Add</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <x-footer></x-footer>
        </div>
    </div>

    <x-main_scripts></x-main_scripts>
</body>
</html>
