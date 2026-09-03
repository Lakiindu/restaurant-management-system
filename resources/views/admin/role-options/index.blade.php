@php
    $panel = $panel ?? (auth()->user()->role->role_name === 'Manager' ? 'manager' : 'admin');
@endphp
@extends($panel === 'manager' ? 'layouts.manager' : 'layouts.admin')

@section('title', 'Role Options')
@section('page-title', 'Role Options (Actions)')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-weight: 700; margin-bottom: 5px; color: #1a1d29;">Page Actions</h4>
            <p style="color: #6b7280; margin-bottom: 0;">Manage actions available on each page (Add, Edit, View, Delete)</p>
        </div>
        <button type="button" class="btn" id="btnAddOption"
            style="background: var(--primary-color); color: #fff; border-radius: 8px; padding: 10px 20px;">
            <i class="bi bi-plus-lg me-1"></i> Add New Option
        </button>
    </div>

    <div class="custom-table mb-4">
        <div style="padding: 20px 25px;">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" id="searchInput" class="form-control"
                        placeholder="🔍 Search by option name or code...">
                </div>
                <div class="col-md-3">
                    <select id="pageFilter" class="form-select">
                        <option value="">All Pages</option>
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

    <div class="custom-table">
        <div class="table-header d-flex justify-content-between align-items-center">
            <div>
                <h6 style="font-weight: 600; margin-bottom: 2px;">Options List</h6>
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
                        <th>Option Name</th>
                        <th>Code</th>
                        <th>Belongs to Page</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="optionsTableBody">
                    <tr>
                        <td colspan="7" class="table-loading">
                            <div class="spinner-custom"></div>
                            <p class="text-muted mt-3">Loading options...</p>
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
    <div class="modal fade" id="optionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="bi bi-lightning-charge-fill me-2"></i>Add New Option
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="optionForm">
                    @csrf
                    <input type="hidden" id="option_id" name="option_id">
                    <input type="hidden" id="form_action" value="create">

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">
                                    Option Name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lightning"></i></span>
                                    <input type="text" name="option_name" id="option_name" class="form-control"
                                        placeholder="e.g. Add User, Export Data" maxlength="45" required>
                                </div>
                                <small class="text-danger error-msg" data-field="option_name"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">
                                    Option Code <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-code"></i></span>
                                    <input type="text" name="option_code" id="option_code" class="form-control"
                                        placeholder="e.g. USER_ADD" style="text-transform: uppercase;" required>
                                </div>
                                <small class="text-danger error-msg" data-field="option_code"></small>
                                <small class="text-muted">UPPERCASE only, use underscores</small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" style="font-weight: 500;">
                                    Belongs to Page <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-file-earmark"></i></span>
                                    <select name="page_id" id="page_id" class="form-select" required>
                                        <option value="">-- Select Page --</option>
                                    </select>
                                </div>
                                <small class="text-danger error-msg" data-field="page_id"></small>
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

                        <div class="mt-3"
                            style="padding: 12px 15px; background: #eff6ff; border-radius: 10px; border-left: 4px solid #3b82f6;">
                            <small style="color: #1e40af;">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Tip:</strong> Options are action buttons like "Add", "Edit", "Delete" that appear on
                                a page. Assign them to roles in the Permissions page.
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius: 8px; padding: 8px 20px;">Cancel</button>
                        <button type="submit" class="btn" id="submitBtn"
                            style="background: var(--primary-color); color: #fff; border-radius: 8px; padding: 8px 20px;">
                            <i class="bi bi-check-lg me-1"></i> <span id="submitText">Create Option</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- VIEW MODAL -->
    <div class="modal fade" id="viewOptionModal" tabindex="-1">
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
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <h4 style="font-weight: 700; margin-bottom: 5px;" id="viewOptionName"></h4>
                        <code
                            style="background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 6px; color: #fff;"
                            id="viewOptionCode"></code>
                    </div>

                    <div style="padding: 30px;">
                        <h6 style="font-weight: 600; margin-bottom: 20px; color: #1a1d29;">
                            <i class="bi bi-info-circle me-2"></i>Option Information
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">BELONGS TO PAGE</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewOptionPage"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">CATEGORY</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewOptionCategory"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">STATUS</small>
                                    <div style="margin-top: 5px;" id="viewOptionStatus"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">CREATED AT</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewOptionCreated"></div>
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

            // 🔒 Pass user permissions to JS
            const userPermissions = {
                canAdd: @json(auth()->user()->hasOptionPermission('OPTION_ADD')),
                canEdit: @json(auth()->user()->hasOptionPermission('OPTION_EDIT')),
                canDelete: @json(auth()->user()->hasOptionPermission('OPTION_DELETE')),
                canView: @json(auth()->user()->hasOptionPermission('OPTION_VIEW')),
            };

            // Hide Add button if no permission
            if (!userPermissions.canAdd) {
                $('#btnAddOption').hide();
            }

            let currentPage = 1;
            let searchTimeout;

            function loadPages() {
                $.ajax({
                    url: `/${panel}/pages/active`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            let filterOpts = '<option value="">All Pages</option>';
                            let formOpts = '<option value="">-- Select Page --</option>';
                            response.data.forEach(function(p) {
                                filterOpts +=
                                    `<option value="${p.page_id}">${p.page_name}</option>`;
                                formOpts +=
                                    `<option value="${p.page_id}">${p.page_name} (${p.page_code})</option>`;
                            });
                            $('#pageFilter').html(filterOpts);
                            $('#page_id').html(formOpts);
                        }
                    }
                });
            }

            function loadOptions(page = 1) {
                currentPage = page;

                $('#optionsTableBody').html(`
            <tr>
                <td colspan="7" class="table-loading">
                    <div class="spinner-custom"></div>
                    <p class="text-muted mt-3">Loading options...</p>
                </td>
            </tr>
        `);

                $.ajax({
                    url: `/${panel}/role-options/fetch`,
                    method: 'GET',
                    data: {
                        page: page,
                        search: $('#searchInput').val(),
                        page_id: $('#pageFilter').val(),
                        status: $('#statusFilter').val()
                    },
                    success: function(response) {
                        renderOptions(response.data, response.pagination);
                        renderPagination(response.pagination);
                        $('#totalCount').text('Total: ' + response.pagination.total + ' options');
                    },
                    error: function() {
                        if (typeof showError === 'function') showError('Failed to load options.');
                        else Swal.fire('Error', 'Failed to load options.', 'error');
                    }
                });
            }

            function renderOptions(options, pagination) {
                if (options.length === 0) {
                    $('#optionsTableBody').html(`
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-lightning-slash" style="font-size: 3rem; color: #d1d5db;"></i>
                        <p class="text-muted mt-2">No options found</p>
                    </td>
                </tr>
            `);
                    return;
                }

                let html = '';
                let startNum = pagination.from;

                options.forEach(function(opt, index) {
                    const statusBadge = opt.status == 1 ?
                        `<span class="badge-active"><i class="bi bi-check-circle-fill me-1"></i>Active</span>` :
                        `<span class="badge-inactive"><i class="bi bi-x-circle-fill me-1"></i>Inactive</span>`;

                    // 🔒 Build action buttons dynamically according to permissions
                    let actionButtons = '';

                    if (userPermissions.canView) {
                        actionButtons += `
                    <li><a class="dropdown-item view-btn" href="#" data-id="${opt.id}">
                        <i class="bi bi-eye me-2"></i>View
                    </a></li>
                `;
                    }

                    if (userPermissions.canEdit) {
                        actionButtons += `
                    <li><a class="dropdown-item edit-btn" href="#" data-id="${opt.id}">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </a></li>
                    <li><a class="dropdown-item toggle-btn" href="#" data-id="${opt.id}">
                        ${opt.status == 1
                            ? '<i class="bi bi-toggle-off me-2"></i>Deactivate'
                            : '<i class="bi bi-toggle-on me-2"></i>Activate'}
                    </a></li>
                `;
                    }

                    if (userPermissions.canDelete) {
                        actionButtons += `
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger delete-btn" href="#"
                           data-id="${opt.id}"
                           data-name="${opt.option_name}">
                        <i class="bi bi-trash me-2"></i>Delete
                    </a></li>
                `;
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
                            <i class="bi bi-lightning-charge-fill me-2" style="color: #f59e0b;"></i>
                            ${opt.option_name}
                        </div>
                    </td>
                    <td>
                        <code style="background: #f3f4f6; padding: 3px 8px; border-radius: 4px; color: #4f46e5; font-size: 0.8rem;">
                            ${opt.option_code}
                        </code>
                    </td>
                    <td>
                        <div>
                            <div style="font-weight: 500;">
                                <i class="bi bi-file-earmark-text me-1 text-primary"></i>${opt.page_name}
                            </div>
                            <small style="color: #6b7280;"><code>${opt.page_code}</code></small>
                        </div>
                    </td>
                    <td>
                        <span class="badge"
                              style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;
                                     border-radius: 8px; padding: 6px 12px;">
                            <i class="bi bi-folder-fill me-1"></i>${opt.category_name}
                        </span>
                    </td>
                    <td>${statusBadge}</td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm" style="background: #f3f4f6; border-radius: 6px;"
                                    data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end"
                                style="border-radius: 10px; border: 1px solid #e5e7eb;">
                                ${actionButtons}
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
                });

                $('#optionsTableBody').html(html);
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
                    loadOptions(page);
                }
            });

            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => loadOptions(1), 500);
            });

            $('#pageFilter, #statusFilter').on('change', () => loadOptions(1));

            $('#resetFilters').on('click', function() {
                $('#searchInput').val('');
                $('#pageFilter').val('');
                $('#statusFilter').val('all');
                loadOptions(1);
            });

            $('#refreshBtn').on('click', function() {
                loadOptions(currentPage);
                if (typeof showToast === 'function') showToast('success', 'Refreshed!');
            });

            $('#option_code').on('input', function() {
                $(this).val($(this).val().toUpperCase().replace(/[^A-Z0-9_]/g, ''));
            });

            $('#btnAddOption').on('click', function() {
                resetForm();
                $('#modalTitle').html('<i class="bi bi-lightning-charge-fill me-2"></i>Add New Option');
                $('#submitText').text('Create Option');
                $('#form_action').val('create');
                $('#option_code').prop('readonly', false);
                $('#optionModal').modal('show');
            });

            // EDIT
            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');

                $.ajax({
                    url: `/${panel}/role-options/${id}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            resetForm();
                            const opt = response.data;
                            $('#option_id').val(opt.id);
                            $('#option_name').val(opt.option_name);
                            $('#option_code').val(opt.option_code).prop('readonly', true);
                            setTimeout(() => $('#page_id').val(opt.page_id), 100);
                            $('#status').val(opt.status);

                            $('#modalTitle').html(
                                '<i class="bi bi-pencil-square me-2"></i>Edit: ' + opt
                                .option_name);
                            $('#submitText').text('Update Option');
                            $('#form_action').val('update');
                            $('#optionModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        if (typeof showError === 'function') showError(xhr.responseJSON
                            ?.message || 'Failed to load option.');
                        else Swal.fire('Error', xhr.responseJSON?.message ||
                            'Failed to load option.', 'error');
                    }
                });
            });

            // VIEW
            $(document).on('click', '.view-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');

                $.ajax({
                    url: `/${panel}/role-options/${id}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const opt = response.data;
                            $('#viewOptionName').text(opt.option_name);
                            $('#viewOptionCode').text(opt.option_code);
                            $('#viewOptionPage').html(
                                '<i class="bi bi-file-earmark-text text-primary me-1"></i>' +
                                opt.page_name);
                            $('#viewOptionCategory').html(
                                '<i class="bi bi-folder-fill text-warning me-1"></i>' + opt
                                .category_name);
                            $('#viewOptionCreated').text(opt.created_at);
                            $('#viewOptionStatus').html(
                                opt.status == 1 ?
                                '<span class="badge-active">Active</span>' :
                                '<span class="badge-inactive">Inactive</span>'
                            );
                            $('#viewOptionModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        if (typeof showError === 'function') showError(xhr.responseJSON
                            ?.message || 'Failed to load option.');
                        else Swal.fire('Error', xhr.responseJSON?.message ||
                            'Failed to load option.', 'error');
                    }
                });
            });

            // SUBMIT
            $('#optionForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-msg').text('');

                const action = $('#form_action').val();
                const id = $('#option_id').val();
                let url = `/${panel}/role-options/store`;
                const formData = new FormData(this);

                if (action === 'update') {
                    url = `/${panel}/role-options/${id}/update`;
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
                            $('#optionModal').modal('hide');
                            if (typeof showToast === 'function') showToast('success', response
                                .message);
                            else Swal.fire('Success', response.message, 'success');
                            loadOptions(currentPage);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(f => {
                                $(`.error-msg[data-field="${f}"]`).text(errors[f][0]);
                            });
                            if (typeof showToast === 'function') showToast('error',
                                'Please fix errors');
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
                            (action === 'create' ? 'Create Option' : 'Update Option') +
                            '</span>'
                        );
                    }
                });
            });

            // DELETE
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const name = $(this).data('name');

                const doDelete = function() {
                    $.ajax({
                        url: `/${panel}/role-options/${id}/delete`,
                        method: 'DELETE',
                        success: function(response) {
                            if (response.success) {
                                if (typeof showToast === 'function') showToast('success',
                                    response.message);
                                loadOptions(currentPage);
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
                    confirmAction('Delete Option?',
                            `Delete "${name}"? This will remove it from all role permissions.`,
                            'Yes, Delete!')
                        .then((r) => {
                            if (r.isConfirmed) doDelete();
                        });
                } else {
                    Swal.fire({
                        title: 'Delete Option?',
                        text: `Delete "${name}"? This will remove it from all role permissions.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Yes, Delete!'
                    }).then((r) => {
                        if (r.isConfirmed) doDelete();
                    });
                }
            });

            // TOGGLE
            $(document).on('click', '.toggle-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');

                const doToggle = function() {
                    $.ajax({
                        url: `/${panel}/role-options/${id}/toggle-status`,
                        method: 'PATCH',
                        success: function(response) {
                            if (response.success) {
                                if (typeof showToast === 'function') showToast('success',
                                    response.message);
                                loadOptions(currentPage);
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
                    confirmAction('Change Status?', 'Change status?', 'Yes!')
                        .then((r) => {
                            if (r.isConfirmed) doToggle();
                        });
                } else {
                    Swal.fire({
                        title: 'Change Status?',
                        text: 'Change status?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Yes!'
                    }).then((r) => {
                        if (r.isConfirmed) doToggle();
                    });
                }
            });

            function resetForm() {
                $('#optionForm')[0].reset();
                $('#option_id').val('');
                $('.error-msg').text('');
            }

            loadPages();
            loadOptions();
        });
    </script>
@endpush
