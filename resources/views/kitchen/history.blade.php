@extends('layouts.' . $panel)

@section('page-title', 'Kitchen History')

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h4 class="fw-bolder text-dark mb-1">Today's Completed Orders</h4>
            <div class="text-muted fs-7">A summary of all food prepared and marked ready today.</div>
        </div>
        <div>
            <div class="bg-white px-4 py-2 border rounded-pill shadow-sm fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fas fa-calendar-day text-primary"></i> {{ \Carbon\Carbon::now()->format('F j, Y') }}
            </div>
        </div>
    </div>

    <div class="bg-white rounded-4 shadow-sm border overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-table">
                <thead class="bg-light">
                    <tr>
                        <th class="text-muted fs-8 fw-bold text-uppercase py-3 ps-4">Order No</th>
                        <th class="text-muted fs-8 fw-bold text-uppercase py-3">Table / Type</th>
                        <th class="text-muted fs-8 fw-bold text-uppercase py-3" style="min-width: 250px;">Items Prepared
                        </th>
                        <th class="text-muted fs-8 fw-bold text-uppercase py-3">Status</th>
                        <th class="text-muted fs-8 fw-bold text-uppercase py-3 pe-4 text-end">Time Completed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bolder text-dark">{{ $order->order_no }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-1">Table {{ $order->table->table_number ?? 'N/A' }}</div>
                                <span
                                    class="badge bg-secondary bg-opacity-10 text-secondary border fs-9 text-uppercase">{{ $order->order_type }}</span>
                            </td>
                            <td>
                                <ul class="list-unstyled mb-0">
                                    @foreach ($order->items as $item)
                                        <li class="fs-8 fw-medium text-dark mb-1 border-bottom pb-1"
                                            style="last-child { border: none; }">
                                            <span class="text-success fw-bold me-1">{{ $item->quantity }}x</span>
                                            {{ $item->menuItem->name ?? 'Dish' }}
                                            @if ($item->kitchen_status === 'Ready')
                                                <i class="fas fa-check-circle text-success ms-1"
                                                    style="font-size: 0.7rem;"></i>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                @if ($order->status === 'Paid' || $order->status === 'Completed')
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"><i
                                            class="fas fa-check me-1"></i> Paid & Done</span>
                                @else
                                    <span
                                        class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1 text-dark"><i
                                            class="fas fa-bell me-1"></i> Ready for Pickup</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="fw-bold text-dark">{{ $order->updated_at->format('h:i A') }}</div>
                                <div class="text-muted fs-9">{{ $order->updated_at->diffForHumans() }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted opacity-50 mb-3"><i class="fas fa-history"
                                        style="font-size: 3rem;"></i></div>
                                <h5 class="fw-bold text-dark">No completed orders yet today.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .custom-table th {
            letter-spacing: 0.5px;
            border-bottom: 2px solid #f1f5f9;
        }

        .custom-table td {
            border-bottom: 1px solid #f1f5f9;
            padding-top: 15px;
            padding-bottom: 15px;
        }
    </style>
@endsection
