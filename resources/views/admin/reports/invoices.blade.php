@extends('layouts.admin')

@section('content')
    @php
        $prefix = request()->is('manager*') ? 'manager' : 'admin';
    @endphp

    <div class="container-fluid py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0 text-dark"><i class="fas fa-file-invoice-dollar text-success me-2"></i>Invoice Report
                </h3>
                <p class="text-muted small mb-0">Paid customer VAT invoices summary and tax records</p>
            </div>
            <div>
                <a href="{{ route($prefix . '.reports.invoices_pdf', request()->all()) }}" target="_blank"
                    class="btn btn-danger btn-sm rounded-pill shadow-sm">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body bg-light rounded-3 p-3">
                <form method="GET" action="{{ route($prefix . '.reports.invoices') }}" class="row g-2">
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
                        <label class="form-label small fw-bold">Invoice / Order #</label>
                        <input type="text" name="invoice_no" class="form-control form-control-sm"
                            placeholder="Search order number..." value="{{ $invoiceNo }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill"><i
                                class="fas fa-search me-1"></i> Filter</button>
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
                            <div class="text-muted small">Total Invoices</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ number_format($kpis['total_invoices']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div
                    class="card shadow-sm border-top-0 border-end-0 border-bottom-0 border-start border-secondary border-4 rounded-3 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3 text-secondary">
                            <i class="fas fa-calculator fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Subtotal</div>
                            <h4 class="fw-bold mb-0 text-dark">Rs {{ number_format($kpis['sub_total'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div
                    class="card shadow-sm border-top-0 border-end-0 border-bottom-0 border-start border-warning border-4 rounded-3 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3 text-warning">
                            <i class="fas fa-percent fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total VAT (18%)</div>
                            <h4 class="fw-bold mb-0 text-dark">Rs {{ number_format($kpis['total_vat'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div
                    class="card shadow-sm border-top-0 border-end-0 border-bottom-0 border-start border-success border-4 rounded-3 h-100">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success">
                            <i class="fas fa-wallet fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Grand Total</div>
                            <h4 class="fw-bold mb-0 text-success">Rs {{ number_format($kpis['grand_total'], 2) }}</h4>
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
                                <th class="ps-3">Invoice #</th>
                                <th>Date & Time</th>
                                <th>Cashier / Waiter</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-end">VAT (18%)</th>
                                <th class="text-end">Grand Total</th>
                                <th class="text-center pe-3">Reprint</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $inv)
                                <tr>
                                    <td class="ps-3 fw-bold text-success">#{{ $inv->order_no }}</td>
                                    <td class="small">{{ $inv->created_at->format('Y-m-d h:i A') }}</td>
                                    <td>{{ $inv->waiter->name ?? 'System' }}</td>
                                    <td class="text-end">Rs {{ number_format($inv->sub_total, 2) }}</td>
                                    <td class="text-end text-warning fw-bold">Rs {{ number_format($inv->vat_amount, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-success">Rs {{ number_format($inv->grand_total, 2) }}
                                    </td>
                                    <td class="text-center pe-3">
                                        <a href="{{ route($prefix . '.pos.receipt', $inv->id) }}?size=a4" target="_blank"
                                            class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-file-invoice me-1"></i> A4 Invoice
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No paid invoices found for this
                                        date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
