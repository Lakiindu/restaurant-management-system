@php
    $panel = $panel ?? (auth()->user()->role->role_name === 'Manager' ? 'manager' : 'admin');
@endphp
@extends($panel === 'manager' ? 'layouts.manager' : 'layouts.admin')

@section('title', 'Inventory Types')
@section('page-title', 'Inventory Types')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-weight: 700; margin-bottom: 5px; color: #1a1d29;">Inventory Types</h4>
            <p style="color: #6b7280; margin-bottom: 0;">Manage inventory item types/categories</p>
        </div>
        <button type="button" class="btn" id="btnAddType"
            style="background: var(--primary-color); color: #fff; border-radius: 8px; padding: 10px 20px;">
            <i class="bi bi-plus-lg me-1"></i> Add New Type
        </button>
    </div>

    <div class="custom-table mb-4">
        <div style="padding: 20px 25px;">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" id="searchInput" class="form-control"
                        placeholder="🔍 Search by type name or description...">
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

    <div class="custom-table">
        <div class="table-header d-flex justify-content-between align-items-center">
            <div>
                <h6 style="font-weight: 600; margin-bottom: 2px;">Types List</h6>
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
                        <th>Type Name</th>
                        <th>Description</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="typesTableBody">
                    <tr>
                        <td colspan="7" class="table-loading">
                            <div class="spinner-custom"></div>
                            <p class="text-muted mt-3">Loading types...</p>
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
    <div class="modal fade" id="typeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="bi bi-folder-plus me-2"></i>Add New Type
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="typeForm">
                    @csrf
                    <input type="hidden" id="type_id" name="type_id">
                    <input type="hidden" id="form_action" value="create">

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label" style="font-weight: 500;">
                                    Type Name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                    <input type="text" name="type_name" id="type_name" class="form-control"
                                        placeholder="e.g. Vegetables, Dairy, Meat" maxlength="50" required>
                                </div>
                                <small class="text-danger error-msg" data-field="type_name"></small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" style="font-weight: 500;">Description</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <textarea name="description" id="description" class="form-control" rows="3"
                                        placeholder="Describe this inventory type..." maxlength="255"></textarea>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" style="font-weight: 500;">
                                    Status <span class="text-danger">*</span>
                                </label>
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
                            style="border-radius: 8px; padding: 8px 20px;">Cancel</button>
                        <button type="submit" class="btn" id="submitBtn"
                            style="background: var(--primary-color); color: #fff; border-radius: 8px; padding: 8px 20px;">
                            <i class="bi bi-check-lg me-1"></i> <span id="submitText">Create Type</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- VIEW MODAL -->
    <div class="modal fade" id="viewTypeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div
                        style="background: linear-gradient(135deg, #4f46e5, #6366f1); padding: 40px; color: #fff; text-align: center; border-radius: 15px 15px 0 0;">
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                            data-bs-dismiss="modal"></button>
                        <div
                            style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 20px;
                                    display: flex; align-items: center; justify-content: center;
                                    margin: 0 auto 15px; font-size: 2.5rem;">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <h4 style="font-weight: 700; margin-bottom: 5px;" id="viewTypeName"></h4>
                        <span class="badge" id="viewTypeStatus"
                            style="background: rgba(255,255,255,0.2); padding: 6px 15px; border-radius: 20px;"></span>
                    </div>

                    <div style="padding: 30px;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">TYPE ID</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewTypeId"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">ITEMS COUNT</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewTypeItems"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">DESCRIPTION</small>
                                    <div style="font-weight: 500; margin-top: 5px;" id="viewTypeDesc"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">CREATED AT</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewTypeCreated"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">LAST UPDATED</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewTypeUpdated"></div>
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
                canAdd: @json(auth()->user()->hasOptionPermission('INV_TYPE_ADD')),
                canEdit: @json(auth()->user()->hasOptionPermission('INV_TYPE_EDIT')),
                canDelete: @json(auth()->user()->hasOptionPermission('INV_TYPE_DELETE')),
                canView: @json(auth()->user()->hasOptionPermission('INV_TYPE_VIEW')),
            };

            if (!userPermissions.canAdd) {
                $('#btnAddType').hide();
            }

            let currentPage = 1;
            let searchTimeout;

            function loadTypes(page = 1) {
                currentPage = page;

                $('#typesTableBody').html(`
            <tr>
                <td colspan="7" class="table-loading">
                    <div class="spinner-custom"></div>
                    <p class="text-muted mt-3">Loading types...</p>
                </td>
            </tr>
        `);

                $.ajax({
                    url: `/${panel}/inventory-types/fetch`,
                    method: 'GET',
                    data: {
                        page: page,
                        search: $('#searchInput').val(),
                        status: $('#statusFilter').val()
                    },
                    success: function(response) {
                        renderTypes(response.data, response.pagination);
                        renderPagination(response.pagination);
                        $('#totalCount').text('Total: ' + response.pagination.total + ' types');
                    },
                    error: function() {
                        if (typeof showError === 'function') showError(
                            'Failed to load inventory types.');
                        else Swal.fire('Error', 'Failed to load inventory types.', 'error');
                    }
                });
            }

            function renderTypes(types, pagination) {
                if (types.length === 0) {
                    $('#typesTableBody').html(`
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #d1d5db;"></i>
                        <p class="text-muted mt-2">No inventory types found</p>
                    </td>
                </tr>
            `);
                    return;
                }

                let html = '';
                let startNum = pagination.from;

                types.forEach(function(type, index) {
                    const statusBadge = type.status == 1 ?
                        `<span class="badge-active"><i class="bi bi-check-circle-fill me-1"></i>Active</span>` :
                        `<span class="badge-inactive"><i class="bi bi-x-circle-fill me-1"></i>Inactive</span>`;

                    let actionButtons = '';

                    if (userPermissions.canView) {
                        actionButtons += `
                    <li><a class="dropdown-item view-btn" href="#" data-id="${type.type_id}">
                        <i class="bi bi-eye me-2"></i>View
                    </a></li>`;
                    }

                    if (userPermissions.canEdit) {
                        actionButtons += `
                    <li><a class="dropdown-item edit-btn" href="#" data-id="${type.type_id}">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </a></li>
                    <li><a class="dropdown-item toggle-btn" href="#" data-id="${type.type_id}">
                        ${type.status == 1
                            ? '<i class="bi bi-toggle-off me-2"></i>Deactivate'
                            : '<i class="bi bi-toggle-on me-2"></i>Activate'}
                    </a></li>`;
                    }

                    if (userPermissions.canDelete) {
                        actionButtons += `
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger delete-btn" href="#"
                           data-id="${type.type_id}"
                           data-name="${type.type_name}"
                           data-items="${type.items_count}">
                        <i class="bi bi-trash me-2"></i>Delete
                    </a></li>`;
                    }

                    if (actionButtons === '') {
                        actionButtons =
                            `<li><span class="dropdown-item text-muted">No actions allowed</span></li>`;
                    }

                    html += `
                <tr>
                    <td>${startNum + index}</td>
                    <td>
                        <div style="font-weight: 600;">
                            <i class="bi bi-box-seam me-2 text-primary"></i>
                            ${type.type_name}
                        </div>
                    </td>
                    <td><small style="color:#6b7280;">${type.description ?? '-'}</small></td>
                    <td>
                        <span class="badge"
                              style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;
                                     border-radius: 8px; padding: 6px 12px; font-weight: 600;">
                            <i class="bi bi-box me-1"></i>${type.items_count}
                        </span>
                    </td>
                    <td>${statusBadge}</td>
                    <td>${type.created_at}</td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm" style="background:#f3f4f6; border-radius:6px;" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end"
                                style="border-radius:10px; border:1px solid #e5e7eb;">
                                ${actionButtons}
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
                });

                $('#typesTableBody').html(html);
            }

            function renderPagination(pagination) {
                if (pagination.last_page <= 1) {
                    $('#paginationContainer').hide();
                    return;
                }

                $('#paginationContainer').css('display', 'flex');
                $('#paginationInfo').text(
                    `Showing ${pagination.from} to ${pagination.to} of ${pagination.total} entries`);

                let html = `
            <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
            </li>
        `;

                for (let i = 1; i <= pagination.last_page; i++) {
                    html += `
                <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
                }

                html += `
            <li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a>
            </li>
        `;

                $('#paginationLinks').html(html);
            }

            $(document).on('click', '#paginationLinks .page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass(
                    'active')) {
                    loadTypes(page);
                }
            });

            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => loadTypes(1), 500);
            });

            $('#statusFilter').on('change', () => loadTypes(1));

            $('#resetFilters').on('click', function() {
                $('#searchInput').val('');
                $('#statusFilter').val('all');
                loadTypes(1);
            });

            $('#refreshBtn').on('click', function() {
                loadTypes(currentPage);
                if (typeof showToast === 'function') showToast('success', 'Refreshed!');
            });

            $('#btnAddType').on('click', function() {
                resetForm();
                $('#modalTitle').html('<i class="bi bi-folder-plus me-2"></i>Add New Type');
                $('#submitText').text('Create Type');
                $('#form_action').val('create');
                $('#typeModal').modal('show');
            });

            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');

                $.ajax({
                    url: `/${panel}/inventory-types/${id}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            resetForm();
                            const type = response.data;
                            $('#type_id').val(type.type_id);
                            $('#type_name').val(type.type_name);
                            $('#description').val(type.description);
                            $('#status').val(type.status);

                            $('#modalTitle').html(
                                '<i class="bi bi-pencil-square me-2"></i>Edit: ' + type
                                .type_name);
                            $('#submitText').text('Update Type');
                            $('#form_action').val('update');
                            $('#typeModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        if (typeof showError === 'function') showError(xhr.responseJSON
                            ?.message || 'Failed to load type.');
                        else Swal.fire('Error', xhr.responseJSON?.message ||
                            'Failed to load type.', 'error');
                    }
                });
            });

            $(document).on('click', '.view-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');

                $.ajax({
                    url: `/${panel}/inventory-types/${id}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const type = response.data;
                            $('#viewTypeName').text(type.type_name);
                            $('#viewTypeId').text('#' + type.type_id);
                            $('#viewTypeItems').html('<i class="bi bi-box me-1"></i>' + type
                                .items_count + ' item(s)');
                            $('#viewTypeDesc').text(type.description || 'No description');
                            $('#viewTypeCreated').text(type.created_at);
                            $('#viewTypeUpdated').text(type.updated_at);
                            $('#viewTypeStatus').text(type.status == 1 ? 'Active' : 'Inactive');
                            $('#viewTypeModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        if (typeof showError === 'function') showError(xhr.responseJSON
                            ?.message || 'Failed to load type.');
                        else Swal.fire('Error', xhr.responseJSON?.message ||
                            'Failed to load type.', 'error');
                    }
                });
            });

            $('#typeForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-msg').text('');

                const action = $('#form_action').val();
                const id = $('#type_id').val();
                let url = `/${panel}/inventory-types/store`;
                const formData = new FormData(this);

                if (action === 'update') {
                    url = `/${panel}/inventory-types/${id}/update`;
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
                            $('#typeModal').modal('hide');
                            if (typeof showToast === 'function') showToast('success', response
                                .message);
                            else Swal.fire('Success', response.message, 'success');
                            loadTypes(currentPage);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(f => {
                                $(`.error-msg[data-field="${f}"]`).text(errors[f][0]);
                            });
                        } else {
                            if (typeof showError === 'function') showError(xhr.responseJSON
                                ?.message || 'Error!');
                            else Swal.fire('Error', xhr.responseJSON?.message || 'Error!',
                                'error');
                        }
                    },
                    complete: function() {
                        $('#submitBtn').prop('disabled', false).html(
                            '<i class="bi bi-check-lg me-1"></i> <span id="submitText">' +
                            (action === 'create' ? 'Create Type' : 'Update Type') +
                            '</span>'
                        );
                    }
                });
            });

            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const name = $(this).data('name');
                const items = $(this).data('items');

                if (items > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cannot Delete!',
                        html: `Type <strong>"${name}"</strong> has <strong>${items}</strong> item(s).<br>Move/delete items first.`,
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                const doDelete = function() {
                    $.ajax({
                        url: `/${panel}/inventory-types/${id}/delete`,
                        method: 'DELETE',
                        success: function(response) {
                            if (response.success) {
                                if (typeof showToast === 'function') showToast('success',
                                    response.message);
                                loadTypes(currentPage);
                            }
                        },
                        error: function(xhr) {
                            if (typeof showError === 'function') showError(xhr.responseJSON
                                ?.message || 'Failed!');
                            else Swal.fire('Error', xhr.responseJSON?.message || 'Failed!',
                                'error');
                        }
                    });
                };

                if (typeof confirmAction === 'function') {
                    confirmAction('Delete Type?', `Delete "${name}"?`, 'Yes, Delete!')
                        .then(r => {
                            if (r.isConfirmed) doDelete();
                        });
                } else {
                    Swal.fire({
                        title: 'Delete Type?',
                        text: `Delete "${name}"?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Yes, Delete!'
                    }).then(r => {
                        if (r.isConfirmed) doDelete();
                    });
                }
            });

            $(document).on('click', '.toggle-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');

                const doToggle = function() {
                    $.ajax({
                        url: `/${panel}/inventory-types/${id}/toggle-status`,
                        method: 'PATCH',
                        success: function(response) {
                            if (response.success) {
                                if (typeof showToast === 'function') showToast('success',
                                    response.message);
                                loadTypes(currentPage);
                            }
                        },
                        error: function(xhr) {
                            if (typeof showError === 'function') showError(xhr.responseJSON
                                ?.message || 'Failed!');
                            else Swal.fire('Error', xhr.responseJSON?.message || 'Failed!',
                                'error');
                        }
                    });
                };

                if (typeof confirmAction === 'function') {
                    confirmAction('Change Status?', 'Change this type status?', 'Yes!')
                        .then(r => {
                            if (r.isConfirmed) doToggle();
                        });
                } else {
                    Swal.fire({
                        title: 'Change Status?',
                        text: 'Change this type status?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Yes!'
                    }).then(r => {
                        if (r.isConfirmed) doToggle();
                    });
                }
            });

            function resetForm() {
                $('#typeForm')[0].reset();
                $('#type_id').val('');
                $('.error-msg').text('');
            }

            loadTypes();
        });
    </script>
@endpush
