@php
    $panel = $panel ?? (auth()->user()->role->role_name === 'Manager' ? 'manager' : 'admin');
@endphp
@extends($panel === 'manager' ? 'layouts.manager' : 'layouts.admin')

@section('title', 'Inventory Items')
@section('page-title', 'Inventory Items')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-weight: 700; margin-bottom: 5px; color: #1a1d29;">Inventory Items</h4>
            <p style="color: #6b7280; margin-bottom: 0;">Manage ingredients, raw materials, and stock levels</p>
        </div>
        <button type="button" class="btn" id="btnAddItem"
            style="background: var(--primary-color); color: #fff; border-radius: 8px; padding: 10px 20px;">
            <i class="bi bi-plus-lg me-1"></i> Add New Item
        </button>
    </div>

    <!-- Filters -->
    <div class="custom-table mb-4">
        <div style="padding: 20px 25px;">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search by name or code...">
                </div>
                <div class="col-md-2">
                    <select id="typeFilter" class="form-select">
                        <option value="">All Types</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="stockFilter" class="form-select">
                        <option value="all">All Stock</option>
                        <option value="low">Low Stock Only</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="statusFilter" class="form-select">
                        <option value="all">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" id="resetFilters" class="btn btn-light w-100" style="border-radius: 8px;">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="custom-table">
        <div class="table-header d-flex justify-content-between align-items-center">
            <div>
                <h6 style="font-weight: 600; margin-bottom: 2px;">Items List</h6>
                <small style="color: #6b7280;" id="totalCount">Loading...</small>
            </div>
            <button type="button" id="refreshBtn" class="btn btn-light btn-sm" style="border-radius: 8px;">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Details</th>
                        <th>Type</th>
                        <th>Cost Price</th>
                        <th>Stock Level</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody">
                    <tr>
                        <td colspan="7" class="table-loading">
                            <div class="spinner-custom"></div>
                            <p class="text-muted mt-3">Loading items...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top: 1px solid #f0f0f0; display:none;" id="paginationContainer">
            <small style="color: #6b7280;" id="paginationInfo"></small>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="paginationLinks"></ul>
            </nav>
        </div>
    </div>

    <!-- ADD/EDIT MODAL -->
    <div class="modal fade" id="itemModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="bi bi-box-seam me-2"></i>Add New Item
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="itemForm">
                    @csrf
                    <input type="hidden" id="item_id" name="item_id">
                    <input type="hidden" id="form_action" value="create">

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">Item Name <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                    <input type="text" name="item_name" id="item_name" class="form-control"
                                        placeholder="e.g. Tomatoes" required>
                                </div>
                                <small class="text-danger error-msg" data-field="item_name"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">Item Code <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                    <input type="text" name="item_code" id="item_code" class="form-control"
                                        placeholder="e.g. VEG-001" style="text-transform: uppercase;" required>
                                </div>
                                <small class="text-danger error-msg" data-field="item_code"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">Inventory Type <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-folder"></i></span>
                                    <select name="type_id" id="type_id" class="form-select" required>
                                        <option value="">-- Select Type --</option>
                                    </select>
                                </div>
                                <small class="text-danger error-msg" data-field="type_id"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">Cost Price <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" step="0.01" name="price" id="price"
                                        class="form-control" placeholder="0.00" required>
                                </div>
                                <small class="text-danger error-msg" data-field="price"></small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" style="font-weight: 500;">Unit of Measurement <span
                                        class="text-danger">*</span></label>
                                <select name="unit" id="unit" class="form-select" required>
                                    <option value="kg">Kilogram (kg)</option>
                                    <option value="g">Gram (g)</option>
                                    <option value="L">Liter (L)</option>
                                    <option value="ml">Milliliter (ml)</option>
                                    <option value="pcs">Pieces (pcs)</option>
                                    <option value="box">Box</option>
                                    <option value="packet">Packet</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" style="font-weight: 500;">Current Stock <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="current_stock" id="current_stock"
                                    class="form-control" value="0" required>
                                <small class="text-danger error-msg" data-field="current_stock"></small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label" style="font-weight: 500;">Minimum Stock Alert <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="minimum_stock" id="minimum_stock"
                                    class="form-control" value="0" required>
                                <small class="text-danger error-msg" data-field="minimum_stock"></small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" style="font-weight: 500;">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" style="font-weight: 500;">Status <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-toggle-on"></i></span>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius: 8px;">Cancel</button>
                        <button type="submit" class="btn" id="submitBtn"
                            style="background: var(--primary-color); color: #fff; border-radius: 8px;">
                            <i class="bi bi-check-lg me-1"></i> <span id="submitText">Create Item</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- VIEW MODAL -->
    <div class="modal fade" id="viewItemModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div
                        style="background: linear-gradient(135deg, #4f46e5, #6366f1); padding: 40px; color: #fff; text-align: center; border-radius: 15px 15px 0 0;">
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                            data-bs-dismiss="modal"></button>
                        <div
                            style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 2.5rem;">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <h4 style="font-weight: 700; margin-bottom: 5px;" id="viewItemName"></h4>
                        <code
                            style="background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 6px; color: #fff;"
                            id="viewItemCode"></code>
                    </div>

                    <div style="padding: 30px;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">TYPE</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewItemType"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">COST PRICE</small>
                                    <div style="font-weight: 600; margin-top: 5px; color: #10b981;" id="viewItemPrice">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">CURRENT STOCK</small>
                                    <div style="font-weight: 700; margin-top: 5px; font-size: 1.1rem;" id="viewItemStock">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">MINIMUM ALERT</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewItemMin"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">STATUS</small>
                                    <div style="margin-top: 5px;" id="viewItemStatus"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">DESCRIPTION</small>
                                    <div style="font-weight: 500; margin-top: 5px;" id="viewItemDesc"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const panel = @json($panel);

            const userPermissions = {
                canAdd: @json(auth()->user()->hasOptionPermission('INV_ITEM_ADD')),
                canEdit: @json(auth()->user()->hasOptionPermission('INV_ITEM_EDIT')),
                canDelete: @json(auth()->user()->hasOptionPermission('INV_ITEM_DELETE')),
                canView: @json(auth()->user()->hasOptionPermission('INV_ITEM_VIEW')),
            };

            if (!userPermissions.canAdd) {
                $('#btnAddItem').hide();
            }

            let currentPage = 1;
            let searchTimeout;

            // Load active inventory types for dropdowns
            function loadTypes() {
                $.ajax({
                    url: `/${panel}/inventory-types/active`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            let filterOpts = '<option value="">All Types</option>';
                            let formOpts = '<option value="">-- Select Type --</option>';
                            response.data.forEach(function(t) {
                                filterOpts +=
                                    `<option value="${t.type_id}">${t.type_name}</option>`;
                                formOpts +=
                                    `<option value="${t.type_id}">${t.type_name}</option>`;
                            });
                            $('#typeFilter').html(filterOpts);
                            $('#type_id').html(formOpts);
                        }
                    }
                });
            }

            function loadItems(page = 1) {
                currentPage = page;
                $('#itemsTableBody').html(
                    `<tr><td colspan="7" class="table-loading"><div class="spinner-custom"></div><p class="text-muted mt-3">Loading items...</p></td></tr>`
                    );

                $.ajax({
                    url: `/${panel}/inventory-items/fetch`,
                    method: 'GET',
                    data: {
                        page: page,
                        search: $('#searchInput').val(),
                        type_id: $('#typeFilter').val(),
                        status: $('#statusFilter').val(),
                        stock_status: $('#stockFilter').val(),
                    },
                    success: function(response) {
                        renderItems(response.data, response.pagination);
                        renderPagination(response.pagination);
                        $('#totalCount').text('Total: ' + response.pagination.total + ' items');
                    },
                    error: function() {
                        if (typeof showError === 'function') showError(
                            'Failed to load inventory items.');
                        else Swal.fire('Error', 'Failed to load inventory items.', 'error');
                    }
                });
            }

            function renderItems(items, pagination) {
                if (items.length === 0) {
                    $('#itemsTableBody').html(
                        `<tr><td colspan="7" class="text-center py-5"><i class="bi bi-box-seam" style="font-size: 3rem; color: #d1d5db;"></i><p class="text-muted mt-2">No items found</p></td></tr>`
                        );
                    return;
                }

                let html = '';
                let startNum = pagination.from;

                items.forEach(function(item, index) {
                    const statusBadge = item.status == 1 ? `<span class="badge-active">Active</span>` :
                        `<span class="badge-inactive">Inactive</span>`;

                    // Low Stock logic
                    let stockHtml =
                        `<span style="font-weight: 600;">${item.current_stock} ${item.unit}</span>`;
                    if (item.is_low_stock) {
                        stockHtml +=
                            `<br><span class="badge bg-danger mt-1" style="font-size: 0.7rem;"><i class="bi bi-exclamation-triangle"></i> Low Stock</span>`;
                    }

                    let actionButtons = '';
                    if (userPermissions.canView) actionButtons +=
                        `<li><a class="dropdown-item view-btn" href="#" data-id="${item.item_id}"><i class="bi bi-eye me-2"></i>View</a></li>`;
                    if (userPermissions.canEdit) {
                        actionButtons +=
                            `<li><a class="dropdown-item edit-btn" href="#" data-id="${item.item_id}"><i class="bi bi-pencil me-2"></i>Edit</a></li>`;
                        actionButtons +=
                            `<li><a class="dropdown-item toggle-btn" href="#" data-id="${item.item_id}">${item.status == 1 ? '<i class="bi bi-toggle-off me-2"></i>Deactivate' : '<i class="bi bi-toggle-on me-2"></i>Activate'}</a></li>`;
                    }
                    if (userPermissions.canDelete) {
                        actionButtons +=
                            `<li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-danger delete-btn" href="#" data-id="${item.item_id}" data-name="${item.item_name}"><i class="bi bi-trash me-2"></i>Delete</a></li>`;
                    }
                    if (actionButtons === '') actionButtons =
                        `<li><span class="dropdown-item text-muted">No actions allowed</span></li>`;

                    html += `
                <tr>
                    <td>${startNum + index}</td>
                    <td>
                        <div style="font-weight: 600; color: var(--primary-color);">${item.item_name}</div>
                        <small style="color: #6b7280;"><code>${item.item_code}</code></small>
                    </td>
                    <td>
                        <span class="badge" style="background: #f3f4f6; color: #374151; padding: 5px 10px;">
                            <i class="bi bi-folder me-1"></i>${item.type_name}
                        </span>
                    </td>
                    <td style="font-weight: 500;">$${item.price}</td>
                    <td>${stockHtml}</td>
                    <td>${statusBadge}</td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm" style="background:#f3f4f6; border-radius:6px;" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end" style="border-radius:10px; border:1px solid #e5e7eb;">${actionButtons}</ul>
                        </div>
                    </td>
                </tr>
            `;
                });
                $('#itemsTableBody').html(html);
            }

            function renderPagination(pagination) {
                if (pagination.last_page <= 1) {
                    $('#paginationContainer').hide();
                    return;
                }
                $('#paginationContainer').css('display', 'flex');
                $('#paginationInfo').text(
                    `Showing ${pagination.from} to ${pagination.to} of ${pagination.total} entries`);

                let html =
                    `<li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a></li>`;
                for (let i = 1; i <= pagination.last_page; i++) {
                    html +=
                        `<li class="page-item ${i === pagination.current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                }
                html +=
                    `<li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a></li>`;
                $('#paginationLinks').html(html);
            }

            $(document).on('click', '#paginationLinks .page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active'))
                    loadItems(page);
            });

            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => loadItems(1), 500);
            });
            $('#typeFilter, #statusFilter, #stockFilter').on('change', () => loadItems(1));
            $('#resetFilters').on('click', function() {
                $('#searchInput').val('');
                $('#typeFilter, #statusFilter, #stockFilter').val('');
                loadItems(1);
            });
            $('#refreshBtn').on('click', function() {
                loadItems(currentPage);
                if (typeof showToast === 'function') showToast('success', 'Refreshed!');
            });

            $('#item_code').on('input', function() {
                $(this).val($(this).val().toUpperCase().replace(/[^A-Z0-9_-]/g, ''));
            });

            $('#btnAddItem').on('click', function() {
                resetForm();
                $('#modalTitle').html('<i class="bi bi-box-seam me-2"></i>Add New Item');
                $('#submitText').text('Create Item');
                $('#form_action').val('create');
                $('#item_code').prop('readonly', false);
                $('#itemModal').modal('show');
            });

            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                $.ajax({
                    url: `/${panel}/inventory-items/${id}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            resetForm();
                            const item = response.data;
                            $('#item_id').val(item.item_id);
                            $('#item_name').val(item.item_name);
                            $('#item_code').val(item.item_code).prop('readonly', true);
                            $('#price').val(item.price);
                            $('#unit').val(item.unit);
                            $('#current_stock').val(item.current_stock);
                            $('#minimum_stock').val(item.minimum_stock);
                            $('#description').val(item.description);
                            $('#status').val(item.status);
                            setTimeout(() => $('#type_id').val(item.type_id), 100);
                            $('#modalTitle').html(
                                '<i class="bi bi-pencil-square me-2"></i>Edit: ' + item
                                .item_name);
                            $('#submitText').text('Update Item');
                            $('#form_action').val('update');
                            $('#itemModal').modal('show');
                        }
                    }
                });
            });

            $(document).on('click', '.view-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                $.ajax({
                    url: `/${panel}/inventory-items/${id}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const item = response.data;
                            $('#viewItemName').text(item.item_name);
                            $('#viewItemCode').text(item.item_code);
                            $('#viewItemType').html(
                                '<i class="bi bi-folder me-1 text-primary"></i>' + item
                                .type_name);
                            $('#viewItemPrice').text('$' + item.price);

                            let stockWarning = item.current_stock <= item.minimum_stock ?
                                ' <span class="badge bg-danger ms-2">Low Stock</span>' : '';
                            $('#viewItemStock').html(item.current_stock + ' ' + item.unit +
                                stockWarning);

                            $('#viewItemMin').text(item.minimum_stock + ' ' + item.unit);
                            $('#viewItemDesc').text(item.description || 'No description');
                            $('#viewItemStatus').html(item.status == 1 ?
                                '<span class="badge-active">Active</span>' :
                                '<span class="badge-inactive">Inactive</span>');
                            $('#viewItemModal').modal('show');
                        }
                    }
                });
            });

            $('#itemForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-msg').text('');
                const action = $('#form_action').val();
                const id = $('#item_id').val();
                let url = `/${panel}/inventory-items/store`;
                const formData = new FormData(this);
                if (action === 'update') {
                    url = `/${panel}/inventory-items/${id}/update`;
                    formData.append('_method', 'PUT');
                }

                $('#submitBtn').prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm"></span> Saving...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#itemModal').modal('hide');
                            if (typeof showToast === 'function') showToast('success', response
                                .message);
                            loadItems(currentPage);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(f => $(`.error-msg[data-field="${f}"]`)
                                .text(errors[f][0]));
                        } else {
                            if (typeof showError === 'function') showError(xhr.responseJSON
                                ?.message || 'Error!');
                        }
                    },
                    complete: function() {
                        $('#submitBtn').prop('disabled', false).html(
                            '<i class="bi bi-check-lg me-1"></i> <span id="submitText">' + (
                                action === 'create' ? 'Create Item' : 'Update Item') +
                            '</span>');
                    }
                });
            });

            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const name = $(this).data('name');

                const doDel = function() {
                    $.ajax({
                        url: `/${panel}/inventory-items/${id}/delete`,
                        method: 'DELETE',
                        success: function(r) {
                            if (r.success) {
                                if (typeof showToast === 'function') showToast('success', r
                                    .message);
                                loadItems(currentPage);
                            }
                        }
                    });
                };

                if (typeof confirmAction === 'function') confirmAction('Delete Item?', `Delete "${name}"?`,
                    'Yes, Delete!').then(r => {
                    if (r.isConfirmed) doDel();
                });
                else Swal.fire({
                    title: 'Delete?',
                    text: `Delete "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444'
                }).then(r => {
                    if (r.isConfirmed) doDel();
                });
            });

            $(document).on('click', '.toggle-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                $.ajax({
                    url: `/${panel}/inventory-items/${id}/toggle-status`,
                    method: 'PATCH',
                    success: function(r) {
                        if (r.success) {
                            loadItems(currentPage);
                        }
                    }
                });
            });

            function resetForm() {
                $('#itemForm')[0].reset();
                $('#item_id').val('');
                $('.error-msg').text('');
            }

            loadTypes();
            loadItems();
        });
    </script>
@endpush
