@extends('layouts.admin')

@section('content')
    @php
        $prefix = request()->is('manager*') ? 'manager' : 'admin';
    @endphp

    <div class="container-fluid py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0 text-dark"><i class="fas fa-credit-card text-info me-2"></i>Payment Receipts Report
                </h3>
                <p class="text-muted small mb-0">Detailed collection records per payment method (Cash, Card, Online)</p>
            </div>
            <div>
                <a href="{{ route($prefix . '.reports.payments_pdf', request()->all()) }}" target="_blank"
                    class="btn btn-danger btn-sm rounded-pill shadow-sm">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body bg-light rounded-3 p-3">
                <form method="GET" action="{{ route($prefix . '.reports.payments') }}" class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">From Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm"
                            value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">To Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm"
                            value="{{ $endDate }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Payment Method</label>
                        <select name="payment_method" class="form-select form-select-sm">
                            <option value="">All Payment Methods</option>
                            <option value="Cash" {{ $method === 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Card" {{ $method === 'Card' ? 'selected' : '' }}>Card</option>
                            <option value="Online" {{ $method === 'Online' ? 'selected' : '' }}>Online / Transfer</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-info text-white btn-sm w-100 rounded-pill"><i
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
                            <i class="fas fa-coins fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Collected</div>
                            <h4 class="fw-bold mb-0 text-dark">Rs {{ number_format($kpis['total_collected'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div
                    class="card shadow-sm border-top-0 border-end-0 border-bottom-0 border-start border-success border-4 rounded-3 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success">
                            <i class="fas fa-money-bill-alt fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Cash Payments</div>
                            <h4 class="fw-bold mb-0 text-success">Rs {{ number_format($kpis['cash_collected'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div
                    class="card shadow-sm border-top-0 border-end-0 border-bottom-0 border-start border-info border-4 rounded-3 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info">
                            <i class="fas fa-credit-card fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Card Payments</div>
                            <h4 class="fw-bold mb-0 text-info">Rs {{ number_format($kpis['card_collected'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div
                    class="card shadow-sm border-top-0 border-end-0 border-bottom-0 border-start border-warning border-4 rounded-3 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3 text-warning">
                            <i class="fas fa-hand-holding-usd fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Change Given</div>
                            <h4 class="fw-bold mb-0 text-dark">Rs {{ number_format($kpis['change_given'], 2) }}</h4>
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
                                <th class="ps-3">Payment ID</th>
                                <th>Order No</th>
                                <th>Date & Time</th>
                                <th>Method</th>
                                <th class="text-end">Amount Paid</th>
                                <th class="text-end">Change Given</th>
                                <th>Staff</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $pmt)
                                <tr>
                                    <td class="ps-3 text-muted">#PAY-{{ $pmt->id }}</td>
                                    <td class="fw-bold text-primary">#{{ $pmt->order->order_no ?? 'N/A' }}</td>
                                    <td class="small">{{ $pmt->created_at->format('Y-m-d h:i A') }}</td>
                                    <td>
                                        @if ($pmt->payment_method === 'Cash')
                                            <span class="badge bg-success"><i class="fas fa-money-bill me-1"></i>
                                                Cash</span>
                                        @elseif($pmt->payment_method === 'Card')
                                            <span class="badge bg-info text-dark"><i class="fas fa-credit-card me-1"></i>
                                                Card</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="fas fa-globe me-1"></i> Online</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">Rs {{ number_format($pmt->amount, 2) }}</td>
                                    <td class="text-end text-muted">Rs {{ number_format($pmt->change_given, 2) }}</td>
                                    <td>{{ $pmt->order->waiter->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No payment transactions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
