@php
    $panel = $panel ?? strtolower(auth()->user()->role->role_name ?? 'admin');
@endphp
@extends('layouts.' . $panel)

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">Menu Items Master</h4>
                <span class="text-muted fs-7">Manage primary selling dishes and recipe linkages</span>
            </div>
            @if (auth()->user()->hasOptionPermission('MENU_ITEM_ADD'))
                <a href="{{ url($panel . '/menu-items/create') }}" class="btn btn-primary shadow-sm"
                    style="border-radius: 6px;">
                    + Add Menu Item
                </a>
            @endif
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
            <div class="card-body py-3">
                <form method="GET" action="{{ url($panel . '/menu-items') }}" class="row g-3 align-items-center">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search name, code..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-secondary w-100">Filter</button>
                        <a href="{{ url($panel . '/menu-items') }}" class="btn btn-sm btn-light border">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card border-0 shadow-sm" style="border-radius: 10px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-muted fs-7 fw-semibold border-bottom ps-4"
                                    style="letter-spacing: 0.5px;">#</th>
                                <th class="text-uppercase text-muted fs-7 fw-semibold border-bottom"
                                    style="letter-spacing: 0.5px;">Item Name</th>
                                <th class="text-uppercase text-muted fs-7 fw-semibold border-bottom"
                                    style="letter-spacing: 0.5px;">Category</th>
                                <th class="text-uppercase text-muted fs-7 fw-semibold border-bottom"
                                    style="letter-spacing: 0.5px;">Price</th>
                                <th class="text-uppercase text-muted fs-7 fw-semibold border-bottom"
                                    style="letter-spacing: 0.5px;">Recipe / Status</th>
                                <th class="text-uppercase text-muted fs-7 fw-semibold border-bottom text-end pe-4"
                                    style="letter-spacing: 0.5px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($menuItems as $item)
                                <tr>
                                    <td class="ps-4 text-muted fs-7">{{ $item->item_code }}</td>
                                    <td>
                                        <div class="text-primary fw-semibold">{{ $item->name }}</div>
                                        @if ($item->is_vegetarian)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill"
                                                style="font-size: 0.65rem;">Veg</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-dark fs-7">{{ $item->category->name ?? 'None' }}</span>
                                    </td>
                                    <td class="text-dark fw-medium fs-7">Rs {{ number_format($item->price, 2) }}</td>
                                    <td>
                                        <div class="d-flex flex-column gap-1 align-items-start">
                                            <span class="text-success fs-7">{{ $item->status }}</span>
                                            @if ($item->ingredients->count() > 0)
                                                <span
                                                    class="badge bg-info bg-opacity-10 text-info rounded text-wrap text-start"
                                                    style="font-size: 0.7rem; max-width: 180px;">
                                                    {{ $item->ingredients->count() }} Ingredients
                                                </span>
                                            @else
                                                <span class="text-muted fs-8">No recipe</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted text-decoration-none"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="fs-5">&#8942;</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                @if (auth()->user()->hasOptionPermission('MENU_ITEM_EDIT'))
                                                    <li><a class="dropdown-item fs-7"
                                                            href="{{ url($panel . '/menu-items/' . $item->id . '/edit') }}">Edit
                                                            Item</a></li>
                                                @endif
                                                @if (auth()->user()->hasOptionPermission('MENU_ITEM_DELETE'))
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li><button class="dropdown-item text-danger fs-7 btn-delete"
                                                            data-id="{{ $item->id }}">Delete Item</button></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted fs-7">No menu items records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $menuItems->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will remove the item and its recipe.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`{{ url($panel . '/menu-items') }}/${id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(r => r.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire('Deleted!', data.message, 'success')
                                            .then(() => location.reload());
                                    }
                                });
                        }
                    });
                });
            });
        });
    </script>
@endsection
