@extends('layouts.' . $panel)
@section('page-title', 'Chef Dashboard')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="bg-white p-4 rounded-4 shadow-sm border border-light d-flex align-items-center gap-3">
                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle fs-3"><i class="fas fa-fire"></i></div>
                <div>
                    <div class="text-muted fw-bold fs-7 text-uppercase">Pending Items</div>
                    <h2 class="fw-bolder mb-0 text-dark">{{ $pendingItems }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white p-4 rounded-4 shadow-sm border border-light d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle fs-3"><i class="fas fa-utensils"></i>
                </div>
                <div>
                    <div class="text-muted fw-bold fs-7 text-uppercase">Cooking Now</div>
                    <h2 class="fw-bolder mb-0 text-dark">{{ $preparingItems }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white p-4 rounded-4 shadow-sm border border-light d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle fs-3"><i
                        class="fas fa-check-double"></i></div>
                <div>
                    <div class="text-muted fw-bold fs-7 text-uppercase">Ready Today</div>
                    <h2 class="fw-bolder mb-0 text-dark">{{ $readyItems }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white p-5 rounded-4 shadow-sm border border-light text-center">
        <h3 class="fw-bolder text-dark mb-2">Welcome to the Kitchen Station</h3>
        <p class="text-muted mb-4">Click below to open the Live Kitchen Display System (KDS) and manage active orders.</p>
        <a href="{{ route($panel . '.kitchen.index') }}" class="btn btn-success px-4 py-2 fw-bold rounded-pill shadow-sm">
            <i class="fas fa-desktop me-2"></i> Open KDS Screen
        </a>
    </div>
@endsection
