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

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Tags</h1>
                    </div>

                    <div class="row">
                        <!-- Create Tag -->
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Create New Tag</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('tags.store') }}">
                                        @csrf
                                        <div class="form-group">
                                            <label>Tag Name</label>
                                            <input type="text" class="form-control" name="name" required placeholder="e.g., VIP, Enterprise, Lead">
                                        </div>
                                        <div class="form-group">
                                            <label>Color</label>
                                            <input type="color" class="form-control" name="color" value="#4e73df" style="height: 40px;">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-plus mr-1"></i>Create Tag</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Tag List -->
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">All Tags ({{ $tags->count() }})</h6>
                                </div>
                                <div class="card-body">
                                    @forelse ($tags as $tag)
                                        <div class="d-flex align-items-center justify-content-between p-3 mb-2 border rounded">
                                            <div class="d-flex align-items-center">
                                                <span class="badge mr-3" style="background-color: {{ $tag->color }}; color: #fff; font-size: 0.9rem; padding: 6px 14px;">
                                                    {{ $tag->name }}
                                                </span>
                                                <span class="text-muted small">{{ $tag->customers_count }} customer{{ $tag->customers_count !== 1 ? 's' : '' }}</span>
                                            </div>
                                            <form action="{{ route('tags.destroy', $tag->id) }}" method="POST" onsubmit="return confirm('Delete this tag?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    @empty
                                        <div class="text-center py-4 text-gray-500">
                                            <i class="fas fa-tags fa-2x mb-2 text-gray-300"></i>
                                            <p>No tags created yet.</p>
                                        </div>
                                    @endforelse
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
