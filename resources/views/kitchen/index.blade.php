@extends('layouts.' . $panel)
@section('page-title', 'Kitchen Display System (KDS)')

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h4 class="fw-bolder text-dark mb-1">Active Orders Queue</h4>
            <div class="text-muted fs-7">Manage and update food preparation status.</div>
        </div>
        <div class="d-flex gap-2">
            <span
                class="badge bg-success bg-opacity-10 text-success px-3 py-2 border border-success border-opacity-25 d-flex align-items-center">
                <i class="fas fa-circle me-2 animate-pulse" style="font-size: 0.5rem;"></i> LIVE POLLING
            </span>
            <button class="btn btn-white border shadow-sm px-3 fw-semibold" onclick="window.location.reload()">
                <i class="fas fa-sync-alt text-muted me-1"></i> Refresh
            </button>
        </div>
    </div>

    @if ($orders->isEmpty())
        <div class="bg-white rounded-4 shadow-sm border p-5 text-center my-4">
            <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
                style="width: 80px; height: 80px;">
                <i class="fas fa-check text-success fs-1"></i>
            </div>
            <h3 class="fw-bolder text-dark">Kitchen is Clear!</h3>
            <p class="text-muted">There are currently no active orders.</p>
        </div>
    @else
        <div class="row g-4 align-items-start">
            @foreach ($orders as $order)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <!-- Clean White Ticket Card -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100"
                        style="border: 1px solid #f1f5f9 !important;">

                        <!-- Top Color Bar Indicator -->
                        <div
                            style="height: 6px; background-color: {{ $order->status === 'Pending' ? '#ef4444' : '#f59e0b' }};">
                        </div>

                        <!-- Ticket Header -->
                        <div
                            class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="fw-bolder text-dark mb-0">Table {{ $order->table->table_number ?? 'N/A' }}</h5>
                                <div class="fs-8 text-muted fw-semibold mt-1">{{ $order->order_no }}</div>
                            </div>
                            <div class="text-end">
                                <span
                                    class="badge bg-light text-dark border fw-bold fs-9 text-uppercase mb-1">{{ $order->order_type }}</span>
                                <div class="fw-bold text-muted fs-8">
                                    {{ $order->created_at->diffForHumans(null, true, true) }}</div>
                            </div>
                        </div>

                        <!-- Line Items -->
                        <div class="card-body p-0">
                            @foreach ($order->items as $item)
                                @if (in_array($item->kitchen_status, ['Pending', 'Preparing']))
                                    <div
                                        class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white ticket-row">
                                        <div class="pe-2">
                                            <div class="fw-bold text-dark fs-7 lh-sm">
                                                <span class="text-success fw-bolder me-1">{{ $item->quantity }}x</span>
                                                {{ $item->menuItem->name ?? 'Dish' }}
                                            </div>
                                            @if ($item->notes)
                                                <div
                                                    class="text-danger fw-semibold fs-8 mt-1 bg-danger bg-opacity-10 px-2 py-1 rounded">
                                                    <i class="fas fa-exclamation-circle"></i> {{ $item->notes }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            @if ($item->kitchen_status === 'Pending')
                                                <button
                                                    class="btn btn-sm btn-light border fw-bold text-danger rounded-pill px-3 shadow-sm text-nowrap"
                                                    onclick="changeItemStatus({{ $item->id }}, 'Preparing')">Start</button>
                                            @elseif($item->kitchen_status === 'Preparing')
                                                <button
                                                    class="btn btn-sm btn-success fw-bold rounded-pill px-3 shadow-sm text-nowrap"
                                                    onclick="changeItemStatus({{ $item->id }}, 'Ready')"><i
                                                        class="fas fa-check"></i> Ready</button>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <style>
        .ticket-row {
            transition: 0.2s;
        }

        .ticket-row:hover {
            background-color: #f8fafc !important;
        }

        .animate-pulse {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        setInterval(() => {
            window.location.reload();
        }, 15000); // 15 sec refresh

        async function changeItemStatus(itemId, newStatus) {
            try {
                const response = await fetch(`{{ route($panel . '.kitchen.updateItem') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({
                        item_id: itemId,
                        status: newStatus
                    })
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    showError(data.message);
                }
            } catch (error) {
                showError('Network connection error.');
            }
        }
    </script>
@endsection
