@php
    $panel = $panel ?? (auth()->user()->role->role_name === 'Manager' ? 'manager' : 'admin');
@endphp
@extends($panel === 'manager' ? 'layouts.manager' : 'layouts.admin')

@section('title', 'Create GRN')
@section('page-title', 'Create GRN')

@push('styles')
    <style>
        .grn-section {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e5e7eb;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .section-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 10px 15px;
            font-size: 0.9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .table-grn th {
            background: #f9fafb;
            font-size: 0.75rem;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 15px;
        }

        .table-grn td {
            vertical-align: middle;
            padding: 10px 15px;
            border-bottom: 1px solid #f3f4f6;
        }

        .table-grn .form-control {
            padding: 8px 12px;
        }

        .totals-box {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 0.95rem;
            color: #374151;
        }

        .totals-row.grand-total {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #fff;
            padding: 15px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 0;
            align-items: center;
        }
    </style>
@endpush

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-weight: 700; margin-bottom: 5px; color: #1a1d29;">Create GRN</h4>
            <p style="color: #6b7280; margin-bottom: 0;">Record received goods, verify quantities, and update pricing</p>
        </div>
        <a href="{{ route($panel . '.grn.index') }}" class="btn btn-light" style="border-radius: 8px;">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <form id="grnForm">
        @csrf

        <!-- ================= HEADER DETAILS ================= -->
        <div class="grn-section">
            <div class="section-label">Header Details</div>

            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label fw-medium text-primary">GRN Type <span class="text-danger">*</span></label>
                    <select name="grn_type" id="grn_type" class="form-select border-primary" required>
                        <option value="Stock GRN">📦 Stock GRN (Updates Inventory)</option>
                        <option value="Non-Stock GRN">🧾 Non-Stock GRN (Expenses/Assets)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium">GRN No.</label>
                    <input type="text" name="grn_no" class="form-control bg-light" value="{{ $grnNo }}" readonly>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium">Store / Warehouse <span class="text-danger">*</span></label>
                    <select name="store_id" class="form-select" required>
                        <option value="">Select store...</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">Search supplier...</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium">GRN Date <span class="text-danger">*</span></label>
                    <input type="date" name="grn_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium">Supplier Invoice No.</label>
                    <input type="text" name="ref_no" class="form-control" placeholder="Enter reference...">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-medium">Note</label>
                    <input type="text" name="note" class="form-control" placeholder="Enter notes...">
                </div>
            </div>
        </div>

        <!-- ================= ITEMS GRID ================= -->
        <div class="grn-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="section-label mb-0">Items</div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRow" style="border-radius: 6px;">
                    <i class="bi bi-plus-lg me-1"></i> Add Row
                </button>
            </div>

            <div class="table-responsive" style="overflow: visible;">
                <table class="table table-grn" id="grnTable">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Item</th>
                            <th style="width: 15%;">Batch / Exp</th>
                            <th style="width: 10%;">Qty</th>
                            <th style="width: 15%;">Unit Cost</th>
                            <th style="width: 15%;">Sales Price <small>(Update)</small></th>
                            <th style="width: 12%; text-align: right;">Amount</th>
                            <th style="width: 8%; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="grnTableBody">
                        <!-- Rows added via JS -->
                    </tbody>
                </table>
            </div>

            <div id="emptyTableState" class="text-center py-4"
                style="background: #f9fafb; border-radius: 8px; border: 1px dashed #e5e7eb;">
                <p class="text-muted mb-0">No items added yet. Click "Add Row" to start.</p>
            </div>
        </div>

        <!-- ================= TOTALS & SUBMIT ================= -->
        <div class="row justify-content-end">
            <div class="col-lg-4 col-md-6">
                <div class="totals-box">
                    <div class="totals-row">
                        <span class="fw-medium">Sub Total</span>
                        <span class="fw-bold">Rs <span id="displaySubTotal">0.00</span></span>
                        <input type="hidden" name="sub_total" id="inputSubTotal" value="0">
                    </div>

                    <div class="totals-row align-items-center">
                        <span class="fw-medium">Discount Amount</span>
                        <input type="number" step="0.01" name="discount_amount" id="inputDiscount"
                            class="form-control text-end" value="0" style="width: 120px; padding: 5px 10px;">
                    </div>

                    <div class="totals-row align-items-center">
                        <span class="fw-medium">VAT / Tax Amount</span>
                        <input type="number" step="0.01" name="vat_amount" id="inputVat"
                            class="form-control text-end" value="0" style="width: 120px; padding: 5px 10px;">
                    </div>

                    <div class="totals-row grand-total mt-4">
                        <span>GRAND TOTAL</span>
                        <span>Rs <span id="displayGrandTotal">0.00</span></span>
                        <input type="hidden" name="grand_total" id="inputGrandTotal" value="0">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route($panel . '.grn.index') }}" class="btn btn-light"
                        style="padding: 12px 25px; border-radius: 8px;">Cancel</a>
                    <button type="submit" class="btn" id="btnSubmit"
                        style="background: #10b981; color: white; padding: 12px 30px; border-radius: 8px; font-weight: 600;">
                        <i class="bi bi-check2-circle me-1"></i> Submit GRN
                    </button>
                </div>
            </div>
        </div>

    </form>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const allItemsData = @json($items);
            let filteredItems = [];
            let rowIdx = 0;

            // Filter Items based on GRN Type
            function filterItemsByType() {
                const grnType = $('#grn_type').val();
                let targetStockType = (grnType === 'Stock GRN') ? 'Stock' : 'Non-Stock';

                filteredItems = allItemsData.filter(item => item.stock_type === targetStockType);

                // Clear table when type changes to prevent mismatched items
                $('#grnTableBody').empty();
                rowIdx = 0;
                addRow();
                calculateGrandTotals();
            }

            // Initialize items and row
            filterItemsByType();

            $('#grn_type').on('change', function() {
                Swal.fire({
                    title: 'Change GRN Type?',
                    text: "This will clear all items currently added to the grid.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'Yes, change it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        filterItemsByType();
                    } else {
                        // Revert selection
                        $(this).val($(this).val() === 'Stock GRN' ? 'Non-Stock GRN' : 'Stock GRN');
                    }
                });
            });

            $('#btnAddRow').click(function() {
                addRow();
            });

            $(document).on('click', '.btn-remove-row', function() {
                $(this).closest('tr').remove();
                checkEmptyState();
                calculateGrandTotals();
            });

            // Auto-fill Cost and Sales Price when Item is selected
            $(document).on('change', '.item-select', function() {
                let row = $(this).closest('tr');
                let selectedId = $(this).val();
                let item = filteredItems.find(i => i.id == selectedId);

                if (item) {
                    row.find('.cost-input').val(item.cost_price);
                    row.find('.sales-input').val(item.sales_price);
                } else {
                    row.find('.cost-input').val(0);
                    row.find('.sales-input').val(0);
                }
                row.find('.calc-input').first().trigger('input');
            });

            // Calculate Row Amount
            $(document).on('input', '.calc-input', function() {
                let row = $(this).closest('tr');
                let qty = parseFloat(row.find('.qty-input').val()) || 0;
                let cost = parseFloat(row.find('.cost-input').val()) || 0;
                let amount = qty * cost;

                row.find('.row-amount').text(amount.toFixed(2));
                calculateGrandTotals();
            });

            $('#inputDiscount, #inputVat').on('input', function() {
                calculateGrandTotals();
            });

            function addRow() {
                $('#emptyTableState').hide();

                let itemOptions = '<option value="">Select item...</option>';
                filteredItems.forEach(item => {
                    let codeStr = item.item_code ? `[${item.item_code}] ` : '';
                    let unitStr = item.unit_measure ? ` (${item.unit_measure})` : '';
                    itemOptions += `<option value="${item.id}">${codeStr}${item.name}${unitStr}</option>`;
                });

                let tr = `
            <tr>
                <td>
                    <select name="items[${rowIdx}][item_id]" class="form-select item-select" required>
                        ${itemOptions}
                    </select>
                </td>
                <td>
                    <input type="text" name="items[${rowIdx}][batch_exp]" class="form-control" placeholder="Batch / Exp">
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${rowIdx}][qty]" class="form-control text-end calc-input qty-input" value="0" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${rowIdx}][cost]" class="form-control text-end calc-input cost-input" value="0" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${rowIdx}][sales_price]" class="form-control text-end sales-input" value="0" required>
                </td>
                <td class="text-end fw-bold text-primary align-middle">
                    Rs <span class="row-amount">0.00</span>
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" tabindex="-1">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;

                $('#grnTableBody').append(tr);
                rowIdx++;
            }

            function checkEmptyState() {
                if ($('#grnTableBody tr').length === 0) {
                    $('#emptyTableState').show();
                } else {
                    $('#emptyTableState').hide();
                }
            }

            function calculateGrandTotals() {
                let subTotal = 0;
                $('.row-amount').each(function() {
                    subTotal += parseFloat($(this).text()) || 0;
                });

                let discount = parseFloat($('#inputDiscount').val()) || 0;
                let vat = parseFloat($('#inputVat').val()) || 0;

                let grandTotal = (subTotal - discount) + vat;
                if (grandTotal < 0) grandTotal = 0;

                $('#displaySubTotal').text(subTotal.toFixed(2));
                $('#displayGrandTotal').text(grandTotal.toFixed(2));
                $('#inputSubTotal').val(subTotal.toFixed(2));
                $('#inputGrandTotal').val(grandTotal.toFixed(2));
            }

            $('#grnForm').submit(function(e) {
                e.preventDefault();

                if ($('#grnTableBody tr').length === 0) {
                    Swal.fire('Warning', 'Please add at least one item to the GRN.', 'warning');
                    return;
                }

                // 🛡️ FRONTEND SECURITY: Full Screen Blocking Loader
                Swal.fire({
                    title: 'Processing GRN...',
                    text: 'Please wait, updating inventory and finalizing calculations.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route($panel . '.grn.store') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                confirmButtonColor: '#10b981',
                                allowOutsideClick: false // Force them to click OK before redirecting
                            }).then(() => {
                                window.location.href =
                                    "{{ route($panel . '.grn.index') }}";
                            });
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 429) {
                            // Handled the double-click 429 error gracefully
                            Swal.fire('Processing',
                                'Your GRN is already being processed. Please wait.', 'info');
                        } else if (xhr.status === 422) {
                            let errStr = '';
                            Object.values(xhr.responseJSON.errors).forEach(err => errStr += err[
                                0] + '<br>');
                            Swal.fire('Validation Error', errStr, 'error');
                        } else {
                            Swal.fire('Error', xhr.responseJSON?.message ||
                                'Something went wrong', 'error');
                        }
                    }
                });
            });
        });
    </script>
@endpush
