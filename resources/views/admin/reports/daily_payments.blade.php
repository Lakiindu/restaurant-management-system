@extends('layouts.admin')

@section('content')
    <div class="container-fluid py-4">
        <!-- Top Header & Date Selector -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h3 class="fw-bold m-0 text-dark">Daily Payment Summary</h3>
                <p class="text-muted small m-0">Real-time revenue, payment method breakdowns, and closing audit</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form method="GET" action="{{ route('admin.reports.daily_payments') }}"
                    class="d-flex align-items-center gap-2">
                    <input type="date" name="date" class="form-control shadow-sm border-0 fw-semibold"
                        value="{{ $date }}" onchange="this.form.submit()">
                </form>
                <a href="{{ route('admin.reports.daily_payments_pdf', ['date' => $date]) }}" target="_blank"
                    class="btn btn-danger shadow-sm fw-semibold">
                    <i class="fas fa-file-pdf me-1"></i> Print / PDF
                </a>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="row g-3 mb-4">
            <!-- Total Revenue -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 p-3 border-start border-4 border-primary bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fw-bold small">Total Revenue</span>
                            <h3 class="fw-bold my-1 text-primary">Rs {{ number_format($totalSales, 2) }}</h3>
                            <small class="text-muted">{{ $totalOrders }} Paid Order(s)</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                            <i class="fas fa-wallet fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cash Collected -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 p-3 border-start border-4 border-success bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fw-bold small">Cash Collected</span>
                            <h3 class="fw-bold my-1 text-success">Rs {{ number_format($cashTotal, 2) }}</h3>
                            <small class="text-muted">Drawer Cash</small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Payments -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 p-3 border-start border-4 border-info bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fw-bold small">Card Payments</span>
                            <h3 class="fw-bold my-1 text-info">Rs {{ number_format($cardTotal, 2) }}</h3>
                            <small class="text-muted">POS Machines</small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info">
                            <i class="fas fa-credit-card fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Online Transfers -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card shadow-sm h-100 p-3 border-start border-4 border-warning bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fw-bold small">Online / Other</span>
                            <h3 class="fw-bold my-1 text-warning">Rs {{ number_format($onlineTotal, 2) }}</h3>
                            <small class="text-muted">Digital Payments</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                            <i class="fas fa-globe fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold m-0 text-dark">Paid Transactions for {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                </h5>
                <span class="badge bg-light text-dark border px-3 py-2">VAT Collected: Rs
                    {{ number_format($totalVat, 2) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Order No</th>
                                <th>Type / Table</th>
                                <th>Time</th>
                                <th>Subtotal</th>
                                <th>VAT (18%)</th>
                                <th>Grand Total</th>
                                <th>Payment Lines</th>
                                <th class="pe-3 text-center">Receipts</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td class="ps-3 fw-bold">{{ $order->order_no }}</td>
                                    <td>
                                        @if ($order->order_type === 'Takeaway')
                                            <span class="badge bg-warning text-dark"><i
                                                    class="fas fa-shopping-bag me-1"></i> Takeaway</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="fas fa-utensils me-1"></i>
                                                {{ $order->table ? $order->table->name : 'Dine-In' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $order->updated_at->format('h:i A') }}</td>
                                    <td>Rs {{ number_format($order->sub_total, 2) }}</td>
                                    <td>Rs {{ number_format($order->vat_amount, 2) }}</td>
                                    <td class="fw-bold text-success">Rs {{ number_format($order->grand_total, 2) }}</td>
                                    <td>
                                        @foreach ($order->payments as $p)
                                            <span class="badge bg-white text-dark border me-1">
                                                {{ $p->payment_method }}: Rs {{ number_format($p->amount, 2) }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="pe-3 text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.pos.receipt', $order->id) }}?size=80mm"
                                                target="_blank" class="btn btn-outline-success">80mm</a>
                                            <a href="{{ route('admin.pos.receipt', $order->id) }}?size=a4" target="_blank"
                                                class="btn btn-outline-primary">A4</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-receipt fa-2x mb-2 d-block opacity-50"></i>
                                        No paid transactions found for this date.
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
