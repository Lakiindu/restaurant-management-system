@php
    $panel = $panel ?? strtolower(auth()->user()?->role?->role_name ?? 'admin');
@endphp
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

                        <!-- CARD HEADER: Dynamic Takeaway vs Dine-In -->
                        <div
                            class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div>
                                <h5 class="fw-bold text-dark mb-0 fs-5">
                                    @if ($order->order_type === 'Takeaway' || !$order->table_id)
                                        <i class="fas fa-shopping-bag text-warning me-1"></i> Takeaway
                                    @else
                                        {{ $order->table ? $order->table->name ?? 'Table ' . $order->table->table_number : 'Dine-In' }}
                                    @endif
                                </h5>
                                <div class="fs-8 text-muted fw-semibold mt-1">{{ $order->order_no }}</div>
                            </div>

                            <div class="text-end">
                                @if ($order->order_type === 'Takeaway' || !$order->table_id)
                                    <span class="badge bg-warning text-dark fw-bold px-2 py-1 mb-1">TAKEAWAY</span>
                                @else
                                    <span class="badge bg-secondary fw-bold px-2 py-1 mb-1">DINE-IN</span>
                                @endif
                                <div class="fw-bold text-muted fs-8">
                                    {{ $order->created_at->diffForHumans(null, true, true) }}
                                </div>
                            </div>
                        </div>

                        <!-- CARD BODY: Items list & Status Action Buttons -->
                        <div class="card-body p-0">
                            @foreach ($order->items as $item)
                                @if (in_array($item->kitchen_status, ['Pending', 'Preparing', 'Ready']))
                                    <div
                                        class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white ticket-row">
                                        <div class="pe-2">
                                            <div class="fw-bold text-dark fs-7 lh-sm mb-1">
                                                <span class="text-success fw-bolder me-1">{{ $item->quantity }}x</span>
                                                {{ $item->menuItem ? $item->menuItem->name : 'Dish' }}
                                            </div>
                                            @if ($item->notes)
                                                <div
                                                    class="text-danger fw-semibold fs-8 mt-1 bg-danger bg-opacity-10 px-2 py-1 rounded">
                                                    <i class="fas fa-exclamation-circle"></i> {{ $item->notes }}
                                                </div>
                                            @endif
                                        </div>

                                        <!-- STEP 1 -> STEP 2 -> STEP 3 BUTTONS -->
                                        <div>
                                            @if ($item->kitchen_status === 'Pending')
                                                <!-- Step 1: Click Start to begin cooking -->
                                                <button
                                                    class="btn btn-sm btn-outline-danger fw-bold rounded-pill px-3 shadow-sm text-nowrap"
                                                    onclick="changeItemStatus({{ $item->id }}, 'Preparing')">
                                                    Start
                                                </button>
                                            @elseif($item->kitchen_status === 'Preparing')
                                                <!-- Step 2: Currently cooking -> Click to mark Ready -->
                                                <button
                                                    class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 shadow-sm text-nowrap"
                                                    onclick="changeItemStatus({{ $item->id }}, 'Ready')">
                                                    <i class="fas fa-spinner fa-spin me-1"></i> Cooking...
                                                </button>
                                            @elseif($item->kitchen_status === 'Ready')
                                                <!-- Step 3: Done -->
                                                <span class="badge bg-success px-2 py-1 rounded-pill shadow-sm">
                                                    <i class="fas fa-check"></i> Ready
                                                </span>
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
                    // Falls back to Swal if showError isn't defined in your master layout
                    if (typeof showError === "function") {
                        showError(data.message);
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                }
            } catch (error) {
                if (typeof showError === "function") {
                    showError('Network connection error.');
                } else {
                    Swal.fire('Error', 'Network connection error.', 'error');
                }
            }
        }
    </script>
@endsection
