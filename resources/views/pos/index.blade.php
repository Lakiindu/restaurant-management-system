@php
    $panel = $panel ?? strtolower(auth()->user()->role->role_name ?? 'admin');
@endphp
@extends('layouts.' . $panel)

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark"><i class="fas fa-th-large me-2"></i> Floor Plan</h4>
                <span class="text-muted fs-7">Select a table to start or manage an order</span>
            </div>
            <div class="d-flex gap-3 align-items-center">
                <div class="d-flex align-items-center gap-1"><span class="badge bg-success p-2 rounded-circle"></span> <small
                        class="text-muted fw-bold">Available</small></div>
                <div class="d-flex align-items-center gap-1"><span class="badge bg-danger p-2 rounded-circle"></span> <small
                        class="text-muted fw-bold">Occupied</small></div>
                <div class="d-flex align-items-center gap-1"><span class="badge bg-warning p-2 rounded-circle"></span>
                    <small class="text-muted fw-bold">Reserved</small></div>
            </div>
        </div>

        <!-- Table Grid -->
        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-4">
            @foreach ($tables as $table)
                @php
                    // Determine Table Color based on status
                    if ($table->status === 'Occupied' || $table->activeOrder) {
                        $bgColor = 'bg-danger text-white';
                        $shadow = 'shadow-sm border-danger';
                        $statusText = 'Occupied';
                        $amount = $table->activeOrder
                            ? 'Rs ' . number_format($table->activeOrder->grand_total, 2)
                            : 'Processing...';
                    } elseif ($table->status === 'Reserved') {
                        $bgColor = 'bg-warning text-dark';
                        $shadow = 'shadow-sm border-warning';
                        $statusText = 'Reserved';
                        $amount = '-';
                    } else {
                        $bgColor = 'bg-white text-dark';
                        $shadow = 'shadow-sm border-success';
                        $statusText = 'Available';
                        $amount = '-';
                    }
                @endphp

                <div class="col">
                    <a href="{{ url($panel . '/pos/order/' . $table->id) }}" class="text-decoration-none">
                        <div class="card h-100 {{ $shadow }} table-card transition-hover"
                            style="border-top: 4px solid; border-radius: 12px; cursor: pointer;">
                            <div class="card-body text-center p-4 {{ $bgColor === 'bg-white text-dark' ? '' : $bgColor }}"
                                style="border-radius: 0 0 10px 10px;">

                                <h3
                                    class="fw-bold mb-1 {{ $bgColor === 'bg-white text-dark' ? 'text-dark' : 'text-white' }}">
                                    {{ $table->table_number }}</h3>
                                <div class="mb-3">
                                    <small
                                        class="fw-semibold px-2 py-1 rounded {{ $bgColor === 'bg-white text-dark' ? 'bg-success bg-opacity-10 text-success' : 'bg-white bg-opacity-25' }}">
                                        {{ $statusText }}
                                    </small>
                                </div>

                                <div
                                    class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top {{ $bgColor === 'bg-white text-dark' ? 'border-light' : 'border-light border-opacity-25' }}">
                                    <div class="fs-7 fw-semibold" title="Capacity">
                                        <i class="fas fa-users me-1"></i> {{ $table->capacity }}
                                    </div>
                                    <div class="fs-7 fw-bold" title="Current Bill">
                                        {{ $amount }}
                                    </div>
                                </div>

                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        .table-card.transition-hover {
            transition: all 0.2s ease-in-out;
        }

        .table-card.transition-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        }
    </style>
@endsection
