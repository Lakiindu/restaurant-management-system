@php
    $panel = $panel ?? (auth()->user()->role->role_name === 'Manager' ? 'manager' : 'admin');
@endphp
@extends($panel === 'manager' ? 'layouts.manager' : 'layouts.admin')

@section('title', 'Items')
@section('page-title', 'Item Master Record')

@push('styles')
    <style>
        .section-title {
            font-weight: 700;
            color: #1a1d29;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            font-size: 1rem;
        }

        .section-title i {
            color: var(--primary-color);
            margin-right: 8px;
            font-size: 1.2rem;
        }

        .form-section {
            background: #f9fafb;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            margin-bottom: 20px;
        }

        .modal-xl {
            max-width: 1140px;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-weight:700;margin-bottom:5px;color:#1a1d29;">Items</h4>
            <p style="color:#6b7280;margin-bottom:0;">Manage primary item records</p>
        </div>
        <button type="button" class="btn" id="btnAdd"
            style="background:var(--primary-color);color:#fff;border-radius:8px;padding:10px 20px;">
            <i class="bi bi-plus-lg me-1"></i> Add Item
        </button>
    </div>

    <div class="custom-table mb-4">
        <div style="padding:20px 25px;">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search name, code...">
                </div>
                <div class="col-md-3">
                    <select id="categoryFilter" class="form-select">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="groupFilter" class="form-select">
                        <option value="">All Groups</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" id="resetFilters" class="btn btn-light w-100" style="border-radius:8px;">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="custom-table">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Group</th>
                        <th>Type / Stock</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="8" class="table-loading">
                            <div class="spinner-custom"></div>
                            <p class="text-muted mt-3">Loading...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top:1px solid #f0f0f0;display:none;" id="paginationContainer">
            <small style="color:#6b7280;" id="paginationInfo"></small>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="paginationLinks"></ul>
            </nav>
        </div>
    </div>

    <!-- Add/Edit Modal (Extra Large) -->
    <div class="modal fade" id="formModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"><i class="bi bi-box-seam me-2"></i>Create item master record
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="dataForm">
                    @csrf
                    <input type="hidden" id="row_id" name="id">
                    <input type="hidden" id="form_action" value="create">

                    <div class="modal-body p-4">

                        <!-- Basic Information -->
                        <div class="form-section">
                            <div class="section-title"><i class="bi bi-box"></i> Basic Information</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Item Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" required>
                                    <small class="text-danger error-msg" data-field="name"></small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Item Type</label>
                                    <select name="item_type_id" id="item_type_id" class="form-select">
                                        <option value="">Select...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Stock Type <span
                                            class="text-danger">*</span></label>
                                    <select name="stock_type" id="stock_type" class="form-select" required>
                                        <option value="Stock">Stock</option>
                                        <option value="Non-Stock">Non-Stock</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Category <span class="text-danger">*</span></label>
                                    <select name="item_category_id" id="item_category_id" class="form-select" required>
                                        <option value="">Select...</option>
                                    </select>
                                    <small class="text-danger error-msg" data-field="item_category_id"></small>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Sub Category</label>
                                    <select name="item_sub_category_id" id="item_sub_category_id" class="form-select">
                                        <option value="">Select...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Item Group</label>
                                    <select name="item_group_id" id="item_group_id" class="form-select">
                                        <option value="">Select...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Item Sub Group</label>
                                    <select name="item_sub_group_id" id="item_sub_group_id" class="form-select">
                                        <option value="">Select...</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Codes & Units -->
                        <div class="form-section">
                            <div class="section-title"><i class="bi bi-upc-scan"></i> Codes, Units & Warranty</div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Item Code</label>
                                    <input type="text" name="item_code" id="item_code" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Bar Code</label>
                                    <input type="text" name="barcode" id="barcode" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Unit Measures <span
                                            class="text-danger">*</span></label>
                                    <select name="unit_measure" id="unit_measure" class="form-select" required>
                                        <option value="">Select...</option>
                                        <option value="Nos">Nos</option>
                                        <option value="Kg">Kg</option>
                                        <option value="Ltr">Ltr</option>
                                        <option value="Pack">Pack</option>
                                        <option value="Box">Box</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Unit Size</label>
                                    <input type="text" name="unit_size" id="unit_size" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Warranty</label>
                                    <select name="warranty" id="warranty" class="form-select">
                                        <option value="">Select...</option>
                                        <option value="6 Months">6 Months</option>
                                        <option value="1 Year">1 Year</option>
                                        <option value="2 Years">2 Years</option>
                                        <option value="No Warranty">No Warranty</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing & Stock -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-section h-100 mb-0">
                                    <div class="section-title"><i class="bi bi-tags"></i> Pricing & Discount
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="form-label fw-medium">Item Cost <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" step="0.01" name="cost_price" id="cost_price"
                                                class="form-control" value="0" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-medium">Sales Price <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" step="0.01" name="sales_price" id="sales_price"
                                                class="form-control" value="0" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-medium">Discount Type</label>
                                            <select name="discount_type" id="discount_type" class="form-select">
                                                <option value="">Select...</option>
                                                <option value="Percentage">Percentage %</option>
                                                <option value="Fixed">Fixed Amount</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-section h-100 mb-0">
                                    <div class="section-title"><i class="bi bi-graph-up-arrow"></i> Stock Controls &
                                        Follow-up</div>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="form-label fw-medium">Re-Order Level</label>
                                            <input type="number" step="0.01" name="reorder_level" id="reorder_level"
                                                class="form-control">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-medium">Max Sales Qty</label>
                                            <input type="number" step="0.01" name="max_sales_qty" id="max_sales_qty"
                                                class="form-control">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-medium">Status <span
                                                    class="text-danger">*</span></label>
                                            <select name="status" id="status" class="form-select" required>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Descriptions -->
                        <div class="form-section">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="section-title mb-0"><i class="bi bi-card-text"></i> Descriptions</div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_separate">
                                    <label class="form-check-label text-muted" for="enable_separate"><small>Enable
                                            separate Office View</small></label>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Item Description - Client View <span
                                            class="text-danger">*</span></label>
                                    <textarea name="client_description" id="client_description" class="form-control" rows="4" required></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium text-muted">Item Description - Office View <span
                                            class="text-danger">*</span></label>
                                    <textarea name="office_description" id="office_description" class="form-control bg-light" rows="4" readonly
                                        required></textarea>
                                    <small class="text-muted">Auto-filled from Client View (Turn on 'Enable separate' to
                                        edit)</small>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius:8px;padding:8px 25px;">Cancel</button>
                        <button type="submit" class="btn" id="submitBtn"
                            style="background:var(--primary-color);color:#fff;border-radius:8px;padding:8px 30px;">
                            <i class="bi bi-save me-1"></i> <span id="submitText">Save Item</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const panel = @json($panel);
            const perms = {
                canAdd: @json(auth()->user()->hasOptionPermission('ITEM_ADD')),
                canEdit: @json(auth()->user()->hasOptionPermission('ITEM_EDIT')),
                canDelete: @json(auth()->user()->hasOptionPermission('ITEM_DELETE')),
                canView: @json(auth()->user()->hasOptionPermission('ITEM_VIEW')),
            };
            if (!perms.canAdd) $('#btnAdd').hide();

            let currentPage = 1,
                searchTimeout;

            // Load Dropdowns
            function initDropdowns() {
                $.get(`/${panel}/item-groups/active`, res => populateSelect('#groupFilter, #item_group_id', res
                    .data, 'All Groups', 'Select Group'));
                $.get(`/${panel}/item-categories/active`, res => populateSelect(
                    '#categoryFilter, #item_category_id', res.data, 'All Categories', 'Select Category'));
                $.get(`/${panel}/item-types/active`, res => populateSelect('#item_type_id', res.data, '',
                    'Select Type'));
            }

            function populateSelect(selector, data, filterLabel, formLabel) {
                let opts = `<option value="">${selector.includes('Filter') ? filterLabel : formLabel}</option>`;
                data.forEach(d => opts += `<option value="${d.id}">${d.name}</option>`);
                $(selector).html(opts);
            }

            // Dependent Dropdowns
            $('#item_group_id').change(function() {
                $.get(`/${panel}/item-sub-groups/active?item_group_id=` + $(this).val(), res =>
                    populateSelect('#item_sub_group_id', res.data, '', 'Select Sub Group'));
            });
            $('#item_category_id').change(function() {
                $.get(`/${panel}/item-sub-categories/active?item_category_id=` + $(this).val(), res =>
                    populateSelect('#item_sub_category_id', res.data, '', 'Select Sub Category'));
            });

            // Description Sync Logic
            $('#client_description').on('input', function() {
                if (!$('#enable_separate').is(':checked')) {
                    $('#office_description').val($(this).val());
                }
            });
            $('#enable_separate').change(function() {
                if ($(this).is(':checked')) {
                    $('#office_description').removeClass('bg-light').prop('readonly', false);
                    $(this).siblings('label').removeClass('text-muted');
                } else {
                    $('#office_description').addClass('bg-light').prop('readonly', true).val($(
                        '#client_description').val());
                    $(this).siblings('label').addClass('text-muted');
                }
            });

            // Main Table Load
            function loadData(page = 1) {
                currentPage = page;
                $('#tableBody').html(
                    `<tr><td colspan="8" class="table-loading"><div class="spinner-custom"></div></td></tr>`);
                $.ajax({
                    url: `/${panel}/items/fetch`,
                    method: 'GET',
                    data: {
                        page,
                        search: $('#searchInput').val(),
                        item_category_id: $('#categoryFilter').val(),
                        item_group_id: $('#groupFilter').val()
                    },
                    success: function(res) {
                        renderTable(res.data, res.pagination);
                        renderPagination(res.pagination);
                        $('#totalCount').text('Total: ' + res.pagination.total + ' items');
                    }
                });
            }

            function renderTable(rows, pagination) {
                if (!rows.length) {
                    $('#tableBody').html(
                        `<tr><td colspan="8" class="text-center py-5 text-muted">No items found</td></tr>`);
                    return;
                }
                let html = '',
                    start = pagination.from;
                rows.forEach((r, i) => {
                    const status = r.status == 1 ? `<span class="badge-active">Active</span>` :
                        `<span class="badge-inactive">Inactive</span>`;
                    let actions = '';
                    if (perms.canView) actions +=
                        `<li><a href="#" class="dropdown-item view-btn" data-id="${r.id}"><i class="bi bi-eye me-2"></i>View</a></li>`;
                    if (perms.canEdit) {
                        actions +=
                            `<li><a href="#" class="dropdown-item edit-btn" data-id="${r.id}"><i class="bi bi-pencil me-2"></i>Edit</a></li>`;
                        actions +=
                            `<li><a href="#" class="dropdown-item toggle-btn" data-id="${r.id}">${r.status == 1 ? '<i class="bi bi-toggle-off me-2"></i>Deactivate' : '<i class="bi bi-toggle-on me-2"></i>Activate'}</a></li>`;
                    }
                    if (perms.canDelete) actions +=
                        `<li><hr class="dropdown-divider"></li><li><a href="#" class="dropdown-item text-danger delete-btn" data-id="${r.id}" data-name="${r.name}"><i class="bi bi-trash me-2"></i>Delete</a></li>`;

                    html += `<tr>
                <td>${start + i}</td>
                <td>
                    <div style="font-weight:600;color:var(--primary-color)">${r.name}</div>
                    <small class="text-muted">${r.item_code}</small>
                </td>
                <td><span class="badge bg-light text-dark border">${r.category_name}</span></td>
                <td><small>${r.group_name}</small></td>
                <td><small>${r.type_name}<br><b>${r.stock_type}</b></small></td>
                <td style="font-weight:600">Rs ${r.sales_price}</td>
                <td>${status}</td>
                <td class="text-end">
                    <div class="dropdown">
                        <button class="btn btn-sm" style="background:#f3f4f6;border-radius:6px;" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">${actions||'<li><span class="dropdown-item text-muted">None</span></li>'}</ul>
                    </div>
                </td>
            </tr>`;
                });
                $('#tableBody').html(html);
            }

            function renderPagination(p) {
                if (p.last_page <= 1) {
                    $('#paginationContainer').hide();
                    return;
                }
                $('#paginationContainer').css('display', 'flex');
                $('#paginationInfo').text(`Showing ${p.from} to ${p.to} of ${p.total}`);
                let html =
                    `<li class="page-item ${p.current_page===1?'disabled':''}"><a class="page-link" href="#" data-page="${p.current_page-1}">Previous</a></li>`;
                for (let i = 1; i <= p.last_page; i++) html +=
                    `<li class="page-item ${i===p.current_page?'active':''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                html +=
                    `<li class="page-item ${p.current_page===p.last_page?'disabled':''}"><a class="page-link" href="#" data-page="${p.current_page+1}">Next</a></li>`;
                $('#paginationLinks').html(html);
            }

            $(document).on('click', '#paginationLinks .page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active'))
                    loadData(page);
            });

            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => loadData(1), 400);
            });
            $('#categoryFilter, #groupFilter').on('change', () => loadData(1));
            $('#resetFilters').on('click', function() {
                $('#searchInput').val('');
                $('#categoryFilter, #groupFilter').val('');
                loadData(1);
            });
            $('#refreshBtn').on('click', () => loadData(currentPage));

            $('#btnAdd').on('click', function() {
                $('#dataForm')[0].reset();
                $('#row_id').val('');
                $('#form_action').val('create');
                $('#modalTitle').html('<i class="bi bi-box-seam me-2"></i>Create Item Master Record');
                $('#submitText').text('Save Item');
                $('#enable_separate').prop('checked', false).trigger('change');
                $('#item_sub_group_id').html('<option value="">Select...</option>');
                $('#item_sub_category_id').html('<option value="">Select...</option>');
                $('#formModal').modal('show');
            });

            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                $.get(`/${panel}/items/${$(this).data('id')}/get`, function(res) {
                    if (!res.success) return;
                    $('#dataForm')[0].reset();
                    const r = res.data;
                    $('#row_id').val(r.id);
                    $('#name').val(r.name);
                    $('#item_type_id').val(r.item_type_id);
                    $('#stock_type').val(r.stock_type);
                    $('#item_category_id').val(r.item_category_id).trigger('change');
                    setTimeout(() => $('#item_sub_category_id').val(r.item_sub_category_id), 300);
                    $('#item_group_id').val(r.item_group_id).trigger('change');
                    setTimeout(() => $('#item_sub_group_id').val(r.item_sub_group_id), 300);

                    $('#item_code').val(r.item_code);
                    $('#barcode').val(r.barcode);
                    $('#unit_measure').val(r.unit_measure);
                    $('#unit_size').val(r.unit_size);
                    $('#warranty').val(r.warranty);

                    $('#cost_price').val(r.cost_price);
                    $('#sales_price').val(r.sales_price);
                    $('#discount_type').val(r.discount_type);

                    $('#reorder_level').val(r.reorder_level);
                    $('#max_sales_qty').val(r.max_sales_qty);

                    $('#client_description').val(r.client_description);
                    $('#office_description').val(r.office_description);

                    if (r.client_description !== r.office_description) {
                        $('#enable_separate').prop('checked', true).trigger('change');
                    } else {
                        $('#enable_separate').prop('checked', false).trigger('change');
                    }

                    $('#status').val(r.status);

                    $('#modalTitle').html('<i class="bi bi-pencil-square me-2"></i>Edit Item');
                    $('#submitText').text('Update Item');
                    $('#form_action').val('update');
                    $('#formModal').modal('show');
                });
            });

            $(document).on('click', '.view-btn', function(e) {
                e.preventDefault();
                $.get(`/${panel}/items/${$(this).data('id')}/get`, function(res) {
                    if (!res.success) return;
                    const r = res.data;
                    Swal.fire({
                        title: r.name,
                        html: `<div style="text-align:left; font-size:0.9rem;">
                    <p><b>Code/Bar:</b> ${r.item_code || '-'} / ${r.barcode || '-'}</p>
                    <p><b>Cat/Group:</b> ${r.category?.name||'-'} / ${r.group?.name||'-'}</p>
                    <p><b>Cost/Sale:</b> Rs ${r.cost_price} / Rs ${r.sales_price}</p>
                    <p><b>Unit:</b> ${r.unit_measure} ${r.unit_size||''}</p>
                    <p><b>Desc:</b> ${r.client_description}</p>
                </div>`
                    });
                });
            });

            $('#dataForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-msg').text('');
                const action = $('#form_action').val();
                const id = $('#row_id').val();
                let url = `/${panel}/items/store`;
                const fd = new FormData(this);
                if (action === 'update') {
                    url = `/${panel}/items/${id}/update`;
                    fd.append('_method', 'PUT');
                }

                $('#submitBtn').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm"></span> Saving...');
                $.ajax({
                    url,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            $('#formModal').modal('hide');
                            if (typeof showToast === 'function') showToast('success', res
                                .message);
                            loadData(currentPage);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) Object.keys(xhr.responseJSON.errors).forEach(
                            f => $(`.error-msg[data-field="${f}"]`).text(xhr.responseJSON
                                .errors[f][0]));
                        else Swal.fire('Error', xhr.responseJSON?.message || 'Failed', 'error');
                    },
                    complete: function() {
                        $('#submitBtn').prop('disabled', false).html(
                            '<i class="bi bi-save me-1"></i> <span id="submitText">Save Item</span>'
                        );
                    }
                });
            });

            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const name = $(this).data('name');
                Swal.fire({
                    title: 'Delete?',
                    text: `Delete item "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444'
                }).then(r => {
                    if (r.isConfirmed) {
                        $.ajax({
                            url: `/${panel}/items/${id}/delete`,
                            method: 'DELETE',
                            success: function(res) {
                                if (res.success) {
                                    showToast('success', res.message);
                                    loadData(currentPage);
                                }
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.toggle-btn', function(e) {
                e.preventDefault();
                $.ajax({
                    url: `/${panel}/items/${$(this).data('id')}/toggle-status`,
                    method: 'PATCH',
                    success: function(res) {
                        if (res.success) loadData(currentPage);
                    }
                });
            });

            initDropdowns();
            loadData();
        });
    </script>
@endpush
