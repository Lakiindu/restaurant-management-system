@extends('layouts.admin')
@section('content')
    @php
        $prefix = request()->is('manager*') ? 'manager' : 'admin';
    @endphp
    <div class="container-fluid py-3">

        <!-- Header -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h3 class="fw-bold mb-0 text-dark"><i class="fas fa-boxes text-warning me-2"></i>Stock & Consumption Report

                </h3>

                <p class="text-muted small mb-0">Live raw ingredient stock levels and sales-driven consumption</p>

            </div>

            <div>

                <a href="{{ route($prefix . '.reports.stock_pdf', request()->all()) }}" target="_blank"
                    class="btn btn-danger btn-sm rounded-pill shadow-sm">

                    <i class="fas fa-file-pdf me-1"></i> Export PDF

                </a>

            </div>

        </div>



        <!-- Filter Bar -->

        <div class="card border-0 shadow-sm rounded-3 mb-4">

            <div class="card-body bg-light rounded-3 p-3">

                <form method="GET" action="{{ route($prefix . '.reports.stock') }}" class="row g-2">

                    <div class="col-md-3">

                        <label class="form-label small fw-bold">Consumption From</label>

                        <input type="date" name="start_date" class="form-control form-control-sm"
                            value="{{ $startDate }}">

                    </div>

                    <div class="col-md-3">

                        <label class="form-label small fw-bold">Consumption To</label>

                        <input type="date" name="end_date" class="form-control form-control-sm"
                            value="{{ $endDate }}">

                    </div>

                    <div class="col-md-4">

                        <label class="form-label small fw-bold">Stock Status Filter</label>

                        <select name="stock_status" class="form-select form-select-sm">

                            <option value="">All Inventory Items</option>

                            <option value="low" {{ $statusFilter === 'low' ? 'selected' : '' }}>Low Stock Alerts Only

                            </option>

                            <option value="out" {{ $statusFilter === 'out' ? 'selected' : '' }}>Out of Stock Only

                            </option>

                            <option value="ok" {{ $statusFilter === 'ok' ? 'selected' : '' }}>Healthy Stock Only

                            </option>

                        </select>

                    </div>

                    <div class="col-md-2 d-flex align-items-end">

                        <button type="submit" class="btn btn-warning text-dark btn-sm w-100 rounded-pill"><i
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

                            <i class="fas fa-cubes fa-2x"></i>

                        </div>

                        <div>

                            <div class="text-muted small">Total Inventory Items</div>

                            <h4 class="fw-bold mb-0 text-dark">{{ number_format($kpis['total_items']) }}</h4>

                        </div>

                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div
                    class="card shadow-sm border-top-0 border-end-0 border-bottom-0 border-start border-warning border-4 rounded-3 h-100">

                    <div class="card-body p-3 d-flex align-items-center">

                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3 text-warning">

                            <i class="fas fa-exclamation-triangle fa-2x"></i>

                        </div>

                        <div>

                            <div class="text-muted small">Low Stock Items</div>

                            <h4 class="fw-bold mb-0 text-warning">{{ number_format($kpis['low_stock_count']) }}</h4>

                        </div>

                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div
                    class="card shadow-sm border-top-0 border-end-0 border-bottom-0 border-start border-danger border-4 rounded-3 h-100">

                    <div class="card-body p-3 d-flex align-items-center">

                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3 text-danger">

                            <i class="fas fa-times-circle fa-2x"></i>

                        </div>

                        <div>

                            <div class="text-muted small">Out of Stock</div>

                            <h4 class="fw-bold mb-0 text-danger">{{ number_format($kpis['out_of_stock']) }}</h4>

                        </div>

                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div
                    class="card shadow-sm border-top-0 border-end-0 border-bottom-0 border-start border-success border-4 rounded-3 h-100">

                    <div class="card-body p-3 d-flex align-items-center">

                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success">

                            <i class="fas fa-calculator fa-2x"></i>

                        </div>

                        <div>

                            <div class="text-muted small">Inventory Valuation</div>

                            <h4 class="fw-bold mb-0 text-success">Rs {{ number_format($kpis['total_valuation'], 2) }}</h4>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <!-- Section A: Current Inventory Status -->

        <div class="card border-0 shadow-sm rounded-3 mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-boxes me-2 text-primary"></i>Section A: Current Raw

                    Material Stock Levels</h5>

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="bg-light">

                            <tr>

                                <th class="ps-3">Item Name</th>

                                <th>Category</th>

                                <th>Unit</th>

                                <th class="text-end">Cost Price</th>

                                <th class="text-end">Current Stock</th>

                                <th class="text-end">Reorder Level</th>

                                <th class="text-center pe-3">Status</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($items as $itm)
                                <tr>

                                    <td class="ps-3 fw-bold">{{ $itm->name }}</td>

                                    <td>{{ $itm->category_name ?? 'N/A' }}</td>

                                    <td><span class="badge bg-light text-dark border">{{ $itm->unit_measure }}</span></td>

                                    <td class="text-end">Rs {{ number_format($itm->cost_price, 2) }}</td>

                                    <td class="text-end fw-bold fs-6">{{ number_format($itm->current_stock, 2) }}</td>

                                    <td class="text-end text-muted">{{ number_format($itm->reorder_level, 2) }}</td>

                                    <td class="text-center pe-3">

                                        <span class="badge {{ $itm->badge_class }}">{{ $itm->stock_status }}</span>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="7" class="text-center py-4 text-muted">No items found matching criteria.

                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>



        <!-- Section B: Ingredient Consumption -->

        <div class="card border-0 shadow-sm rounded-3">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-fire me-2 text-danger"></i>Section B: Ingredient

                    Consumption ({{ $startDate }} to {{ $endDate }})</h5>

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="bg-light">

                            <tr>

                                <th class="ps-3">Ingredient Name</th>

                                <th>Unit Measure</th>

                                <th class="text-end pe-3">Total Consumed (Deducted)</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($consumption as $c)
                                <tr>

                                    <td class="ps-3 fw-bold">{{ $c->item_name }}</td>

                                    <td><span class="badge bg-light text-dark border">{{ $c->unit_measure }}</span></td>

                                    <td class="text-end pe-3 fw-bold text-danger">

                                        {{ number_format($c->total_consumed, 2) }} {{ $c->unit_measure }}</td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="3" class="text-center py-4 text-muted">No ingredient consumption

                                        recorded in this period.</td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>
@endsection
