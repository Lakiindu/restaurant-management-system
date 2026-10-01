@php
    $panel = $panel ?? (auth()->user()->role->role_name === 'Manager' ? 'manager' : 'admin');
@endphp
@extends($panel === 'manager' ? 'layouts.manager' : 'layouts.admin')

@section('title', 'Menu Items')
@section('page-title', 'Menu Items')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-weight: 700; margin-bottom: 5px; color: #1a1d29;">Menu Items</h4>
            <p style="color: #6b7280; margin-bottom: 0;">Manage dishes and drinks available to customers</p>
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
                <div class="col-md-3">
                    <input type="text" id="searchInput" class="form-control"
                        placeholder="🔍 Search item name or code...">
                </div>
                <div class="col-md-3">
                    <select id="categoryFilter" class="form-select">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="vegFilter" class="form-select">
                        <option value="all">All Dietary</option>
                        <option value="1">Veg Only</option>
                        <option value="0">Non-Veg Only</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="statusFilter" class="form-select">
                        <option value="all">All Status</option>
                        <option value="1">Available</option>
                        <option value="0">Sold Out</option>
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
                        <th>Category</th>
                        <th>Price</th>
                        <th>Dietary</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody">
                    <tr>
                        <td colspan="7" class="table-loading">
                            <div class="spinner-custom"></div>
                            <p class="text-muted mt-3">Loading menu items...</p>
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
                        <i class="bi bi-cup-hot me-2"></i>Add New Menu Item
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
                                    <span class="input-group-text"><i class="bi bi-fonts"></i></span>
                                    <input type="text" name="item_name" id="item_name" class="form-control"
                                        placeholder="e.g. Chicken Fried Rice" maxlength="100" required>
                                </div>
                                <small class="text-danger error-msg" data-field="item_name"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">Item Code <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                    <input type="text" name="item_code" id="item_code" class="form-control"
                                        placeholder="e.g. FR-CHK-01" style="text-transform: uppercase;" required>
                                </div>
                                <small class="text-danger error-msg" data-field="item_code"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">Category <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-grid"></i></span>
                                    <select name="category_id" id="category_id" class="form-select" required>
                                        <option value="">-- Select Category --</option>
                                    </select>
                                </div>
                                <small class="text-danger error-msg" data-field="category_id"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">Price <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" step="0.01" name="price" id="price"
                                        class="form-control" placeholder="0.00" required>
                                </div>
                                <small class="text-danger error-msg" data-field="price"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">Dietary Type <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-egg-fried"></i></span>
                                    <select name="is_vegetarian" id="is_vegetarian" class="form-select" required>
                                        <option value="0">Non-Vegetarian 🥩</option>
                                        <option value="1">Vegetarian 🍃</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">Status <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-toggle-on"></i></span>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="1">Available</option>
                                        <option value="0">Sold Out / Hidden</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" style="font-weight: 500;">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="2"
                                    placeholder="Ingredients, spice level, etc..."></textarea>
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
                            <i class="bi bi-cup-hot"></i>
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
                                    <small style="color: #6b7280; font-weight: 500;">CATEGORY</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewItemCat"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">PRICE</small>
                                    <div style="font-weight: 700; margin-top: 5px; color: #10b981; font-size: 1.1rem;"
                                        id="viewItemPrice"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">DIETARY</small>
                                    <div style="margin-top: 5px;" id="viewItemVeg"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
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
                canAdd: @json(auth()->user()->hasOptionPermission('MENU_ITEM_ADD')),
                canEdit: @json(auth()->user()->hasOptionPermission('MENU_ITEM_EDIT')),
                canDelete: @json(auth()->user()->hasOptionPermission('MENU_ITEM_DELETE')),
                canView: @json(auth()->user()->hasOptionPermission('MENU_ITEM_VIEW')),
            };

            if (!userPermissions.canAdd) {
                $('#btnAddItem').hide();
            }

            let currentPage = 1;
            let searchTimeout;

            // Load active menu categories for dropdown
            function loadCategories() {
                $.ajax({
                    url: `/${panel}/menu-categories/active`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            let filterOpts = '<option value="">All Categories</option>';
                            let formOpts = '<option value="">-- Select Category --</option>';
                            response.data.forEach(function(c) {
                                filterOpts +=
                                    `<option value="${c.category_id}">${c.category_name}</option>`;
                                formOpts +=
                                    `<option value="${c.category_id}">${c.category_name}</option>`;
                            });
                            $('#categoryFilter').html(filterOpts);
                            $('#category_id').html(formOpts);
                        }
                    }
                });
            }

            function loadItems(page = 1) {
                currentPage = page;
                $('#itemsTableBody').html(
                    `<tr><td colspan="7" class="table-loading"><div class="spinner-custom"></div><p class="text-muted mt-3">Loading menu items...</p></td></tr>`
                );

                $.ajax({
                    url: `/${panel}/menu-items/fetch`,
                    method: 'GET',
                    data: {
                        page: page,
                        search: $('#searchInput').val(),
                        category_id: $('#categoryFilter').val(),
                        status: $('#statusFilter').val(),
                        is_vegetarian: $('#vegFilter').val(),
                    },
                    success: function(response) {
                        renderItems(response.data, response.pagination);
                        renderPagination(response.pagination);
                        $('#totalCount').text('Total: ' + response.pagination.total + ' items');
                    },
                    error: function() {
                        if (typeof showError === 'function') showError('Failed to load menu items.');
                        else Swal.fire('Error', 'Failed to load menu items.', 'error');
                    }
                });
            }

            function renderItems(items, pagination) {
                if (items.length === 0) {
                    $('#itemsTableBody').html(
                        `<tr><td colspan="7" class="text-center py-5"><i class="bi bi-cup-straw" style="font-size: 3rem; color: #d1d5db;"></i><p class="text-muted mt-2">No items found</p></td></tr>`
                    );
                    return;
                }

                let html = '';
                let startNum = pagination.from;

                items.forEach(function(item, index) {
                    const statusBadge = item.status == 1 ? `<span class="badge-active">Available</span>` :
                        `<span class="badge-inactive">Sold Out</span>`;
                    const vegBadge = item.is_vegetarian == 1 ?
                        `<span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981;">🍃 Veg</span>` :
                        `<span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444;">🥩 Non-Veg</span>`;

                    let actionButtons = '';
                    if (userPermissions.canView) actionButtons +=
                        `<li><a class="dropdown-item view-btn" href="#" data-id="${item.item_id}"><i class="bi bi-eye me-2"></i>View</a></li>`;
                    if (userPermissions.canEdit) {
                        actionButtons +=
                            `<li><a class="dropdown-item edit-btn" href="#" data-id="${item.item_id}"><i class="bi bi-pencil me-2"></i>Edit</a></li>`;
                        actionButtons +=
                            `<li><a class="dropdown-item toggle-btn" href="#" data-id="${item.item_id}">${item.status == 1 ? '<i class="bi bi-toggle-off me-2"></i>Mark Sold Out' : '<i class="bi bi-toggle-on me-2"></i>Mark Available'}</a></li>`;
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
                            <i class="bi bi-grid me-1"></i>${item.category_name}
                        </span>
                    </td>
                    <td style="font-weight: 600;">$${item.price}</td>
                    <td>${vegBadge}</td>
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
            $('#categoryFilter, #statusFilter, #vegFilter').on('change', () => loadItems(1));
            $('#resetFilters').on('click', function() {
                $('#searchInput').val('');
                $('#categoryFilter, #statusFilter, #vegFilter').val('');
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
                $('#modalTitle').html('<i class="bi bi-cup-hot me-2"></i>Add New Menu Item');
                $('#submitText').text('Create Item');
                $('#form_action').val('create');
                $('#item_code').prop('readonly', false);
                $('#itemModal').modal('show');
            });

            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                $.ajax({
                    url: `/${panel}/menu-items/${id}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            resetForm();
                            const item = response.data;
                            $('#item_id').val(item.item_id);
                            $('#item_name').val(item.item_name);
                            $('#item_code').val(item.item_code).prop('readonly', true);
                            $('#price').val(item.price);
                            $('#description').val(item.description);
                            $('#is_vegetarian').val(item.is_vegetarian);
                            $('#status').val(item.status);
                            setTimeout(() => $('#category_id').val(item.category_id), 100);
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
                    url: `/${panel}/menu-items/${id}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const item = response.data;
                            $('#viewItemName').text(item.item_name);
                            $('#viewItemCode').text(item.item_code);
                            $('#viewItemCat').html(
                                '<i class="bi bi-grid me-1 text-primary"></i>' + item
                                .category_name);
                            $('#viewItemPrice').text('$' + item.price);
                            $('#viewItemVeg').html(item.is_vegetarian == 1 ?
                                '<span class="badge bg-success">🍃 Vegetarian</span>' :
                                '<span class="badge bg-danger">🥩 Non-Vegetarian</span>');
                            $('#viewItemDesc').text(item.description || 'No description');
                            $('#viewItemStatus').html(item.status == 1 ?
                                '<span class="badge-active">Available</span>' :
                                '<span class="badge-inactive">Sold Out</span>');
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
                let url = `/${panel}/menu-items/store`;
                const formData = new FormData(this);
                if (action === 'update') {
                    url = `/${panel}/menu-items/${id}/update`;
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
                        url: `/${panel}/menu-items/${id}/delete`,
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
                Swal.fire({
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
                    url: `/${panel}/menu-items/${id}/toggle-status`,
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

            loadCategories();
            loadItems();
        });
    </script>
@endpush
