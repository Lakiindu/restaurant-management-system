@php
    $panel = $panel ?? strtolower(auth()->user()->role->role_name ?? 'admin');
@endphp
@extends('layouts.' . $panel)

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">Create Menu Item</h4>
                <span class="text-muted fs-7">Add a new dish and link inventory items</span>
            </div>
            <a href="{{ url($panel . '/menu-items') }}" class="btn btn-sm btn-light border shadow-sm">
                Cancel & Back
            </a>
        </div>

        <form id="menuItemForm" enctype="multipart/form-data">
            @csrf
            <div class="row g-4">
                <!-- Basic Info -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="fw-bold mb-0 text-dark">Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fs-7 fw-semibold text-dark">Item Code <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="item_code" class="form-control form-control-sm"
                                    value="{{ $autoCode }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fs-7 fw-semibold text-dark">Dish Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-sm"
                                    placeholder="e.g. Chicken Fried Rice" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fs-7 fw-semibold text-dark">Category <span
                                        class="text-danger">*</span></label>
                                <select name="category_id" class="form-select form-select-sm" required>
                                    <option value="">Select Category...</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fs-7 fw-semibold text-dark">Selling Price (LKR) <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="price" class="form-control form-control-sm"
                                    placeholder="0.00" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fs-7 fw-semibold text-dark">Description</label>
                                <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fs-7 fw-semibold text-dark">Dish Photo</label>
                                <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                                <small class="text-muted">JPG/PNG/WEBP, max 2MB. Shown on POS cards.</small>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="form-label fs-7 fw-semibold text-dark">Status</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="Active">Active</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="col-6 pt-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_vegetarian" value="1"
                                            id="vegCheck">
                                        <label class="form-check-label fs-7 ms-1" for="vegCheck">Vegetarian</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recipe Builder -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div
                            class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Recipe Link (Auto-Deduction)</h6>
                                <small class="text-muted" style="font-size: 0.7rem;">Ingredients deduct from stock on
                                    sale</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary px-3 rounded-pill" id="btnAddIngredient">
                                + Add Row
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-borderless align-middle mb-0" id="ingredientsTable">
                                <thead class="bg-light border-bottom">
                                    <tr>
                                        <th class="text-uppercase text-muted fs-8 fw-semibold ps-4" style="width: 55%;">Raw
                                            Inventory Item</th>
                                        <th class="text-uppercase text-muted fs-8 fw-semibold" style="width: 35%;">Qty Per
                                            Portion</th>
                                        <th class="text-uppercase text-muted fs-8 fw-semibold text-center"
                                            style="width: 10%;">Remove</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="empty-row text-center border-bottom">
                                        <td colspan="3" class="text-muted fs-7 py-4">No recipe ingredients added.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-light border-top text-end py-3 pe-4">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btnSubmit">
                                Save Record
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <template id="ingredientRowTemplate">
        <tr class="border-bottom">
            <td class="ps-4 py-3">
                <select class="form-select form-select-sm ingredient-select" name="ingredients[INDEX][inventory_item_id]"
                    required>
                    <option value="">Select Inventory Item...</option>
                    @foreach ($inventoryItems as $inv)
                        <option value="{{ $inv->id }}" data-unit="{{ $inv->unit_measure ?? 'Units' }}">
                            {{ $inv->name }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="py-3">
                <div class="input-group input-group-sm">
                    <input type="number" step="0.001" min="0.001" class="form-control"
                        name="ingredients[INDEX][quantity]" placeholder="0.000" required>
                    <span class="input-group-text bg-light unit-badge text-muted"
                        style="font-size: 0.75rem; width: 50px; justify-content: center;">Unit</span>
                </div>
            </td>
            <td class="text-center py-3">
                <button type="button" class="btn btn-sm btn-light text-danger border btn-remove-row rounded-circle px-2">
                    &#10005;
                </button>
            </td>
        </tr>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let rowIndex = 0;
            const tableBody = document.querySelector('#ingredientsTable tbody');
            const template = document.getElementById('ingredientRowTemplate').innerHTML;

            document.getElementById('btnAddIngredient').addEventListener('click', function() {
                const emptyRow = tableBody.querySelector('.empty-row');
                if (emptyRow) emptyRow.remove();
                tableBody.insertAdjacentHTML('beforeend', template.replace(/INDEX/g, rowIndex++));
            });

            tableBody.addEventListener('change', function(e) {
                if (e.target.classList.contains('ingredient-select')) {
                    const unit = e.target.options[e.target.selectedIndex].getAttribute('data-unit') ||
                        'Unit';
                    e.target.closest('tr').querySelector('.unit-badge').textContent = unit;
                }
            });

            tableBody.addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-row')) {
                    e.target.closest('tr').remove();
                    if (tableBody.querySelectorAll('tr').length === 0) {
                        tableBody.innerHTML =
                            `<tr class="empty-row text-center border-bottom"><td colspan="3" class="text-muted fs-7 py-4">No recipe ingredients added.</td></tr>`;
                    }
                }
            });

            document.getElementById('menuItemForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('btnSubmit');
                btn.disabled = true;
                btn.textContent = 'Saving...';
                fetch('{{ url($panel . '/menu-items') }}', {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        window.location.href = '{{ url($panel . '/menu-items') }}';
                    } else {
                        Swal.fire('Error', data.message, 'error');
                        btn.disabled = false;
                        btn.textContent = 'Save Record';
                    }
                });
            });
        });
    </script>
@endsection
