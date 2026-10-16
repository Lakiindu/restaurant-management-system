@php
    $panel = $panel ?? strtolower(auth()->user()->role->role_name ?? 'admin');
@endphp
@extends('layouts.' . $panel)

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">Edit Menu Item: {{ $menuItem->name }}</h3>
                <p class="text-muted small mb-0">Update item pricing or adjust linked recipe quantities</p>
            </div>
            <a href="{{ url($panel . '/menu-items') }}" class="btn btn-outline-secondary px-4">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <form id="menuItemEditForm">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <!-- Left Side: Basic Info -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3">
                            <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-utensils me-2"></i> Dish Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Item Code <span class="text-danger">*</span></label>
                                <input type="text" name="item_code" class="form-control"
                                    value="{{ $menuItem->item_code }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Dish Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $menuItem->name }}"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">-- Select Category --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ $menuItem->category_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Selling Price (LKR) <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs</span>
                                    <input type="number" step="0.01" name="price" class="form-control"
                                        value="{{ $menuItem->price }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ $menuItem->description }}</textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input" type="checkbox" name="is_vegetarian" value="1"
                                            id="vegCheck" {{ $menuItem->is_vegetarian ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="vegCheck">Vegetarian Dish</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="Active" {{ $menuItem->status === 'Active' ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="Inactive" {{ $menuItem->status === 'Inactive' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Recipe Builder -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-0 text-success"><i class="fas fa-mortar-pestle me-2"></i> Recipe
                                    Ingredients</h5>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" id="btnAddIngredient">
                                <i class="fas fa-plus me-1"></i> Add Ingredient
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0" id="ingredientsTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 50%;">Raw Inventory Item</th>
                                            <th style="width: 35%;">Quantity Per Portion</th>
                                            <th style="width: 15%; text-align: center;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($menuItem->ingredients as $idx => $ing)
                                            <tr>
                                                <td>
                                                    <select class="form-select ingredient-select"
                                                        name="ingredients[{{ $idx }}][inventory_item_id]"
                                                        required>
                                                        <option value="">-- Select Inventory Item --</option>
                                                        @foreach ($inventoryItems as $inv)
                                                            <option value="{{ $inv->id }}"
                                                                data-unit="{{ $inv->unit_measure ?? 'Units' }}"
                                                                {{ $ing->inventory_item_id == $inv->id ? 'selected' : '' }}>
                                                                {{ $inv->name }} (Code: {{ $inv->item_code }} | Stock:
                                                                {{ $inv->current_stock }} {{ $inv->unit_measure }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="number" step="0.001" min="0.001"
                                                            class="form-control"
                                                            name="ingredients[{{ $idx }}][quantity]"
                                                            value="{{ $ing->quantity }}" required>
                                                        <span
                                                            class="input-group-text unit-badge">{{ $ing->inventoryItem->unit_measure ?? 'Unit' }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger btn-remove-row"><i
                                                            class="fas fa-times"></i></button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="empty-row text-center">
                                                <td colspan="3" class="text-muted py-4">No recipe ingredients added.
                                                    Click "Add Ingredient" above.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-end py-3">
                            <button type="submit" class="btn btn-primary px-5" id="btnSubmit">
                                <i class="fas fa-save me-1"></i> Update Menu Item
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Template -->
    <template id="ingredientRowTemplate">
        <tr>
            <td>
                <select class="form-select ingredient-select" name="ingredients[INDEX][inventory_item_id]" required>
                    <option value="">-- Select Inventory Item --</option>
                    @foreach ($inventoryItems as $inv)
                        <option value="{{ $inv->id }}" data-unit="{{ $inv->unit_measure ?? 'Units' }}">
                            {{ $inv->name }} (Code: {{ $inv->item_code }} | Stock: {{ $inv->current_stock }}
                            {{ $inv->unit_measure }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" step="0.001" min="0.001" class="form-control"
                        name="ingredients[INDEX][quantity]" placeholder="0.000" required>
                    <span class="input-group-text unit-badge">Unit</span>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i
                        class="fas fa-times"></i></button>
            </td>
        </tr>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let rowIndex = {{ $menuItem->ingredients->count() }};
            const tableBody = document.querySelector('#ingredientsTable tbody');
            const template = document.getElementById('ingredientRowTemplate').innerHTML;

            document.getElementById('btnAddIngredient').addEventListener('click', function() {
                const emptyRow = tableBody.querySelector('.empty-row');
                if (emptyRow) emptyRow.remove();

                const rowHtml = template.replace(/INDEX/g, rowIndex);
                tableBody.insertAdjacentHTML('beforeend', rowHtml);
                rowIndex++;
            });

            tableBody.addEventListener('change', function(e) {
                if (e.target.classList.contains('ingredient-select')) {
                    const selectedOpt = e.target.options[e.target.selectedIndex];
                    const unit = selectedOpt.getAttribute('data-unit') || 'Unit';
                    const row = e.target.closest('tr');
                    row.querySelector('.unit-badge').textContent = unit;
                }
            });

            tableBody.addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-row')) {
                    e.target.closest('tr').remove();
                    if (tableBody.querySelectorAll('tr').length === 0) {
                        tableBody.innerHTML =
                            `<tr class="empty-row text-center"><td colspan="3" class="text-muted py-4">No recipe ingredients added. Click "Add Ingredient" above.</td></tr>`;
                    }
                }
            });

            document.getElementById('menuItemEditForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const btnSubmit = document.getElementById('btnSubmit');
                btnSubmit.disabled = true;

                fetch('{{ url($panel . '/menu-items/' . $menuItem->id) }}', {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        btnSubmit.disabled = false;
                        if (data.success) {
                            Swal.fire('Updated!', data.message, 'success').then(() => {
                                window.location.href = '{{ url($panel . '/menu-items') }}';
                            });
                        } else {
                            Swal.fire('Error', data.message || 'Validation failed', 'error');
                        }
                    })
                    .catch(err => {
                        btnSubmit.disabled = false;
                        Swal.fire('Error', 'Something went wrong!', 'error');
                    });
            });
        });
    </script>
@endsection
