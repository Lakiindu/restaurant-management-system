@extends('layouts.admin')

@section('content')
    @php
        $prefix = request()->is('manager*') ? 'manager' : 'admin';
    @endphp

    <div class="container-fluid py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0 text-dark"><i class="fas fa-shopping-cart text-primary me-2"></i>Orders Report</h3>
                <p class="text-muted small mb-0">Comprehensive record of all dine-in and takeaway orders</p>
            </div>
            <div>
                <a href="{{ route($prefix . '.reports.orders_pdf', request()->all()) }}" target="_blank"
                    class="btn btn-danger btn-sm rounded-pill shadow-sm">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body bg-light rounded-3 p-3">
                <form method="GET" action="{{ route($prefix . '.reports.orders') }}" class="row g-2">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">From Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm"
                            value="{{ $startDate }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">To Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm"
                            value="{{ $endDate }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="Paid" {{ $status === 'Paid' ? 'selected' : '' }}>Paid</option>
                            <option value="Completed" {{ $status === 'Completed' ? 'selected' : '' }}>Completed</option>
                            <option value="Pending" {{ $status === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Preparing" {{ $status === 'Preparing' ? 'selected' : '' }}>Preparing</option>
                            <option value="Cancelled" {{ $status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Order Type</label>
                        <select name="order_type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="Dine-in" {{ $orderType === 'Dine-in' ? 'selected' : '' }}>Dine-in</option>
                            <option value="Takeaway" {{ $orderType === 'Takeaway' ? 'selected' : '' }}>Takeaway</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Waiter</label>
                        <select name="waiter_id" class="form-select form-select-sm">
                            <option value="">All Staff</option>
                            @foreach ($waiters as $w)
                                <option value="{{ $w->id }}" {{ $waiterId == $w->id ? 'selected' : '' }}>
                                    {{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill"><i
                                class="fas fa-filter me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div
                    class="card shadow-sm border-top-0 border-end-0 border-bottom-0 border-start border-primary border-4 rounded-3 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary">
                            <i class="fas fa-receipt fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Orders</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ number_format($kpis['total_orders']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div
                    class="card shadow-sm border-top-0 border-end-0 border-bottom-0 border-start border-success border-4 rounded-3 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Revenue</div>
                            <h4 class="fw-bold mb-0 text-success">Rs {{ number_format($kpis['total_revenue'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div
                    class="card shadow-sm border-top-0 border-end-0 border-bottom-0 border-start border-info border-4 rounded-3 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Paid / Completed</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ number_format($kpis['paid_orders']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div
                    class="card shadow-sm border-top-0 border-end-0 border-bottom-0 border-start border-danger border-4 rounded-3 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3 text-danger">
                            <i class="fas fa-ban fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Cancelled Orders</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ number_format($kpis['cancelled_orders']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Order No</th>
                                <th>Date & Time</th>
                                <th>Type / Table</th>
                                <th>Waiter</th>
                                <th>Status</th>
                                <th class="text-end">Grand Total</th>
                                <th class="text-center pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $ord)
                                <tr>
                                    <td class="ps-3 fw-bold text-primary">#{{ $ord->order_no }}</td>
                                    <td class="small">{{ $ord->created_at->format('Y-m-d h:i A') }}</td>
                                    <td>
                                        @if ($ord->order_type === 'Takeaway' || !$ord->table_id)
                                            <span class="badge bg-warning text-dark"><i
                                                    class="fas fa-shopping-bag me-1"></i> Takeaway</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="fas fa-chair me-1"></i>
                                                {{ $ord->table->table_number ?? 'Dine-in' }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $ord->waiter->name ?? 'System' }}</td>
                                    <td>
                                        @if (in_array($ord->status, ['Paid', 'Completed']))
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($ord->status === 'Cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                        @else
                                            <span class="badge bg-info text-dark">{{ $ord->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">Rs {{ number_format($ord->grand_total, 2) }}</td>
                                    <td class="text-center pe-3">
                                        <a href="{{ route($prefix . '.pos.receipt', $ord->id) }}?size=80mm"
                                            target="_blank" class="btn btn-sm btn-outline-secondary me-1"
                                            title="Receipt 80mm">
                                            <i class="fas fa-print"></i> 80mm
                                        </a>
                                        <a href="{{ route($prefix . '.pos.receipt', $ord->id) }}?size=a4" target="_blank"
                                            class="btn btn-sm btn-outline-info" title="Invoice A4">
                                            <i class="fas fa-file-invoice"></i> A4
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No orders found matching
                                        criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
