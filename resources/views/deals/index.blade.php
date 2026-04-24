<!DOCTYPE html>
<html lang="en">
<head>
    @extends('layouts.head')
    <style>
        .kanban-board { display: flex; gap: 1rem; overflow-x: auto; padding-bottom: 1rem; min-height: 500px; }
        .kanban-column { min-width: 280px; max-width: 300px; flex-shrink: 0; background: #f8f9fc; border-radius: 8px; display: flex; flex-direction: column; }
        .kanban-column-header { padding: 0.75rem 1rem; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; border-radius: 8px 8px 0 0; color: #fff; display: flex; justify-content: space-between; align-items: center; }
        .kanban-column-body { padding: 0.5rem; flex: 1; min-height: 100px; }
        .kanban-card { background: #fff; border-radius: 6px; padding: 0.75rem; margin-bottom: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); cursor: grab; transition: box-shadow 0.2s, transform 0.2s; border-left: 3px solid transparent; }
        .kanban-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.15); transform: translateY(-1px); }
        .kanban-card.dragging { opacity: 0.5; transform: rotate(2deg); }
        .kanban-card .deal-title { font-weight: 600; font-size: 0.9rem; margin-bottom: 0.25rem; }
        .kanban-card .deal-value { font-size: 0.85rem; color: #1cc88a; font-weight: 700; }
        .kanban-card .deal-customer { font-size: 0.75rem; color: #858796; }
        .kanban-card .deal-date { font-size: 0.7rem; color: #b7b9cc; }
        .kanban-column-count { background: rgba(255,255,255,0.3); border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; }
        .drop-zone-active { background: #e8f5e9 !important; }
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
                        <h1 class="h3 mb-0 text-gray-800">Deals Pipeline</h1>
                        <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#newDealModal">
                            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i>New Deal
                        </button>
                    </div>

                    <!-- Pipeline Stats -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Open Deals</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalOpen }}</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-handshake fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Pipeline Value</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalValue, 0) }}</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Won This Month</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $wonThisMonth }}</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-trophy fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Lost This Month</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lostThisMonth }}</div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-times-circle fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kanban Board -->
                    <div class="kanban-board">
                        @foreach ($stages as $stage)
                            <div class="kanban-column" data-stage-id="{{ $stage->id }}">
                                <div class="kanban-column-header" style="background-color: {{ $stage->color }};">
                                    {{ $stage->name }}
                                    <span class="kanban-column-count">{{ $stage->deals->count() }}</span>
                                </div>
                                <div class="kanban-column-body"
                                     ondrop="dropDeal(event)" ondragover="allowDrop(event)"
                                     data-stage-id="{{ $stage->id }}">
                                    @foreach ($stage->deals as $deal)
                                        <div class="kanban-card" draggable="true" ondragstart="dragDeal(event)"
                                             data-deal-id="{{ $deal->id }}" style="border-left-color: {{ $stage->color }};">
                                            <a href="{{ route('deals.show', $deal->id) }}" class="text-decoration-none text-dark">
                                                <div class="deal-title">{{ $deal->title }}</div>
                                                <div class="deal-value">Rp {{ number_format($deal->value, 0) }}</div>
                                                @if($deal->customer)
                                                    <div class="deal-customer"><i class="fas fa-user fa-sm mr-1"></i>{{ $deal->customer->fname }} {{ $deal->customer->lname }}</div>
                                                @endif
                                                @if($deal->expected_close_date)
                                                    <div class="deal-date mt-1"><i class="fas fa-calendar fa-sm mr-1"></i>{{ $deal->expected_close_date->format('M d, Y') }}</div>
                                                @endif
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
            <x-footer></x-footer>
        </div>
    </div>

    <!-- New Deal Modal -->
    <div class="modal fade" id="newDealModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Deal</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST" action="{{ route('deals.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Deal Title</label>
                            <input type="text" class="form-control" name="title" required placeholder="e.g., Website Redesign for Acme Corp">
                        </div>
                        <div class="form-group">
                            <label>Customer</label>
                            <select class="form-control" name="customer_id">
                                <option value="">— No Customer —</option>
                                @foreach ($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->fname }} {{ $c->lname }} {{ $c->company ? '('.$c->company.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Stage</label>
                                    <select class="form-control" name="deal_stage_id" required>
                                        @foreach ($stages as $stage)
                                            <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Value (Rp)</label>
                                    <input type="number" class="form-control" name="value" required min="0" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Expected Close Date</label>
                            <input type="date" class="form-control" name="expected_close_date">
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Create Deal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-main_scripts></x-main_scripts>

    <script>
        // Drag & Drop for Kanban
        function dragDeal(event) {
            event.dataTransfer.setData('dealId', event.target.dataset.dealId);
            event.target.classList.add('dragging');
        }

        function allowDrop(event) {
            event.preventDefault();
            event.currentTarget.classList.add('drop-zone-active');
        }

        function dropDeal(event) {
            event.preventDefault();
            event.currentTarget.classList.remove('drop-zone-active');
            const dealId = event.dataTransfer.getData('dealId');
            const newStageId = event.currentTarget.dataset.stageId;
            const card = document.querySelector(`[data-deal-id="${dealId}"]`);

            if (card) {
                event.currentTarget.appendChild(card);
                // Remove dragging class
                card.classList.remove('dragging');

                // AJAX update stage
                fetch(`/deals/${dealId}/stage`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ deal_stage_id: newStageId })
                }).then(r => r.json()).then(data => {
                    if (!data.success) alert('Failed to update deal stage');
                    else location.reload();
                }).catch(() => alert('Error updating deal stage'));
            }
        }

        // Remove active class on drag leave
        document.querySelectorAll('.kanban-column-body').forEach(col => {
            col.addEventListener('dragleave', function() {
                this.classList.remove('drop-zone-active');
            });
        });
    </script>
</body>
</html>
