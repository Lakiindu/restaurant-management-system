@php
    // Added nullsafe operators (?->) to prevent crashes if user/role is missing
    $panel = $panel ?? strtolower(auth()->user()?->role?->role_name ?? 'admin');
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
                <div class="d-flex align-items-center gap-1">
                    <span class="badge bg-success p-2 rounded-circle"></span>
                    <small class="text-muted fw-bold">Available</small>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span class="badge bg-danger p-2 rounded-circle"></span>
                    <small class="text-muted fw-bold">Occupied</small>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span class="badge bg-warning p-2 rounded-circle"></span>
                    <small class="text-muted fw-bold">Reserved</small>
                </div>

                <!-- New Takeaway Order Button -->
                <form
                    action="{{ route(auth()->user()->role_id == 1 ? 'admin.pos.takeaway.create' : (auth()->user()->role_id == 2 ? 'manager.pos.takeaway.create' : 'waiter.pos.takeaway.create')) }}"
                    method="POST" class="d-inline ms-2">
                    @csrf
                    <button type="submit" class="btn btn-warning fw-bold text-dark px-3 py-2 shadow-sm border-0">
                        <i class="fas fa-shopping-bag me-1"></i> + New Takeaway Order
                    </button>
                </form>
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
                        // Added ?? 0 to prevent PHP 8 TypeError if grand_total is null
                        $amount = $table->activeOrder
                            ? 'Rs ' . number_format($table->activeOrder->grand_total ?? 0, 2)
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
                    <!-- UPDATED: Table Card Link using pos.table.open -->
                    <a href="{{ route(auth()->user()->role_id == 1 ? 'admin.pos.table.open' : (auth()->user()->role_id == 2 ? 'manager.pos.table.open' : 'waiter.pos.table.open'), $table->id) }}"
                        class="text-decoration-none">
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

    <!-- SweetAlert Receipt Print Logic -->
    <script>
        // You can call this function from your checkout AJAX success callback
        function handlePaymentSuccess(orderId, changeDue, paymentCount) {
            Swal.fire({
                title: 'Payment Successful!',
                html: `<h3 class="text-success">Change Due: Rs ${changeDue.toFixed(2)}</h3><p>${paymentCount} payment line(s) recorded</p>`,
                icon: 'success',
                showDenyButton: true, // Enables A4 Invoice button
                showCancelButton: true, // Enables Skip button
                confirmButtonText: '<i class="fas fa-receipt"></i> Print 80mm',
                denyButtonText: '<i class="fas fa-file-invoice"></i> Print A4',
                cancelButtonText: 'Skip & Return',
                confirmButtonColor: '#198754', // Green
                denyButtonColor: '#0dcaf0', // Info/Blue
                cancelButtonColor: '#6c757d', // Gray
            }).then((result) => {
                // Detect current route prefix (admin, manager, or waiter)
                const currentPath = window.location.pathname;
                let prefix = 'admin';
                if (currentPath.includes('/manager/')) prefix = 'manager';
                if (currentPath.includes('/waiter/')) prefix = 'waiter';

                const floorUrl = `/${prefix}/pos`;

                if (result.isConfirmed) {
                    // 80mm thermal receipt
                    window.open(`/${prefix}/pos/receipt/${orderId}?size=80mm`, '_blank');
                    setTimeout(() => {
                        window.location.href = floorUrl;
                    }, 400);
                } else if (result.isDenied) {
                    // A4 invoice
                    window.open(`/${prefix}/pos/receipt/${orderId}?size=a4`, '_blank');
                    setTimeout(() => {
                        window.location.href = floorUrl;
                    }, 400);
                } else {
                    // Skip button clicked -> Go to floor plan
                    window.location.href = floorUrl;
                }
            });
        }
    </script>
@endsection
