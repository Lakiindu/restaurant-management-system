@php
    $panel = $panel ?? (auth()->user()->role->role_name === 'Manager' ? 'manager' : 'admin');
@endphp
@extends($panel === 'manager' ? 'layouts.manager' : 'layouts.admin')

@section('title', 'Page Categories')
@section('page-title', 'Page Categories')

@section('content')

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-weight: 700; margin-bottom: 5px; color: #1a1d29;">Page Categories</h4>
            <p style="color: #6b7280; margin-bottom: 0;">Organize system pages into categories</p>
        </div>
        <button type="button" class="btn" id="btnAddCategory"
            style="background: var(--primary-color); color: #fff; border-radius: 8px; padding: 10px 20px;">
            <i class="bi bi-plus-lg me-1"></i> Add New Category
        </button>
    </div>

    <!-- Filters -->
    <div class="custom-table mb-4">
        <div style="padding: 20px 25px;">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" id="searchInput" class="form-control"
                        placeholder="🔍 Search by name or description...">
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

    <!-- Categories Table -->
    <div class="custom-table">
        <div class="table-header d-flex justify-content-between align-items-center">
            <div>
                <h6 style="font-weight: 600; margin-bottom: 2px;">Categories List</h6>
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
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Pages</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="categoriesTableBody">
                    <tr>
                        <td colspan="7" class="table-loading">
                            <div class="spinner-custom"></div>
                            <p class="text-muted mt-3">Loading categories...</p>
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
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="bi bi-folder-plus me-2"></i>Add New Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="categoryForm">
                    @csrf
                    <input type="hidden" id="category_id" name="category_id">
                    <input type="hidden" id="form_action" value="create">

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label" style="font-weight: 500;">
                                    Category Name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-folder"></i></span>
                                    <input type="text" name="category_name" id="category_name" class="form-control"
                                        placeholder="e.g. Menu Management, Reports" maxlength="45" required>
                                </div>
                                <small class="text-danger error-msg" data-field="category_name"></small>
                                <small class="text-muted">Max 45 characters</small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" style="font-weight: 500;">Description</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <textarea name="description" id="description" class="form-control" rows="3"
                                        placeholder="Describe this category..." maxlength="255"></textarea>
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
                            <i class="bi bi-check-lg me-1"></i> <span id="submitText">Create Category</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- VIEW MODAL -->
    <div class="modal fade" id="viewCategoryModal" tabindex="-1">
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
                            <i class="bi bi-folder-fill"></i>
                        </div>
                        <h4 style="font-weight: 700; margin-bottom: 5px;" id="viewCategoryName"></h4>
                        <span class="badge" id="viewCategoryStatus"
                            style="background: rgba(255,255,255,0.2); padding: 6px 15px; border-radius: 20px;"></span>
                    </div>

                    <div style="padding: 30px;">
                        <h6 style="font-weight: 600; margin-bottom: 20px; color: #1a1d29;">
                            <i class="bi bi-info-circle me-2"></i>Category Information
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">CATEGORY ID</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewCategoryId"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">PAGES COUNT</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewCategoryPages"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">DESCRIPTION</small>
                                    <div style="font-weight: 500; margin-top: 5px;" id="viewCategoryDesc"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">CREATED AT</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewCategoryCreated"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">LAST UPDATED</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewCategoryUpdated"></div>
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
                canAdd: @json(auth()->user()->hasOptionPermission('CATEGORY_ADD')),
                canEdit: @json(auth()->user()->hasOptionPermission('CATEGORY_EDIT')),
                canDelete: @json(auth()->user()->hasOptionPermission('CATEGORY_DELETE')),
                canView: @json(auth()->user()->hasOptionPermission('CATEGORY_VIEW')),
            };

            // Hide Add button if no permission
            if (!userPermissions.canAdd) {
                $('#btnAddCategory').hide();
            }

            let currentPage = 1;
            let searchTimeout;

            function loadCategories(page = 1) {
                currentPage = page;

                $('#categoriesTableBody').html(`
            <tr>
                <td colspan="7" class="table-loading">
                    <div class="spinner-custom"></div>
                    <p class="text-muted mt-3">Loading categories...</p>
                </td>
            </tr>
        `);

                $.ajax({
                    url: `/${panel}/page-categories/fetch`,
                    method: 'GET',
                    data: {
                        page: page,
                        search: $('#searchInput').val(),
                        status: $('#statusFilter').val()
                    },
                    success: function(response) {
                        renderCategories(response.data, response.pagination);
                        renderPagination(response.pagination);
                        $('#totalCount').text('Total: ' + response.pagination.total + ' categories');
                    },
                    error: function() {
                        if (typeof showError === 'function') showError('Failed to load categories.');
                        else Swal.fire('Error', 'Failed to load categories.', 'error');
                    }
                });
            }

            function renderCategories(cats, pagination) {
                if (cats.length === 0) {
                    $('#categoriesTableBody').html(`
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-folder-x" style="font-size: 3rem; color: #d1d5db;"></i>
                        <p class="text-muted mt-2">No categories found</p>
                    </td>
                </tr>
            `);
                    return;
                }

                let html = '';
                let startNum = pagination.from;

                cats.forEach(function(cat, index) {
                    const statusBadge = cat.status == 1 ?
                        `<span class="badge-active"><i class="bi bi-check-circle-fill me-1"></i>Active</span>` :
                        `<span class="badge-inactive"><i class="bi bi-x-circle-fill me-1"></i>Inactive</span>`;

                    // 🔒 Build action buttons based on permissions
                    let actionButtons = '';

                    if (userPermissions.canView) {
                        actionButtons += `
                    <li><a class="dropdown-item view-btn" href="#" data-id="${cat.category_id}">
                        <i class="bi bi-eye me-2"></i>View
                    </a></li>
                `;
                    }

                    if (userPermissions.canEdit) {
                        actionButtons += `
                    <li><a class="dropdown-item edit-btn" href="#" data-id="${cat.category_id}">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </a></li>
                    <li><a class="dropdown-item toggle-btn" href="#" data-id="${cat.category_id}">
                        ${cat.status == 1
                            ? '<i class="bi bi-toggle-off me-2"></i>Deactivate'
                            : '<i class="bi bi-toggle-on me-2"></i>Activate'}
                    </a></li>
                `;
                    }

                    if (userPermissions.canDelete) {
                        actionButtons += `
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger delete-btn" href="#"
                           data-id="${cat.category_id}"
                           data-name="${cat.category_name}"
                           data-pages="${cat.pages_count}">
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
                            <i class="bi bi-folder-fill me-2 text-warning"></i>
                            ${cat.category_name}
                        </div>
                    </td>
                    <td><small style="color: #6b7280;">${cat.description ?? '-'}</small></td>
                    <td>
                        <span class="badge"
                              style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;
                                     border-radius: 8px; padding: 6px 12px; font-weight: 600;">
                            <i class="bi bi-file-earmark me-1"></i> ${cat.pages_count}
                        </span>
                    </td>
                    <td>${statusBadge}</td>
                    <td>${cat.created_at}</td>
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

                $('#categoriesTableBody').html(html);
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
                    loadCategories(page);
                }
            });

            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => loadCategories(1), 500);
            });

            $('#statusFilter').on('change', () => loadCategories(1));

            $('#resetFilters').on('click', function() {
                $('#searchInput').val('');
                $('#statusFilter').val('all');
                loadCategories(1);
            });

            $('#refreshBtn').on('click', function() {
                loadCategories(currentPage);
                if (typeof showToast === 'function') showToast('success', 'Refreshed!');
            });

            $('#btnAddCategory').on('click', function() {
                resetForm();
                $('#modalTitle').html('<i class="bi bi-folder-plus me-2"></i>Add New Category');
                $('#submitText').text('Create Category');
                $('#form_action').val('create');
                $('#categoryModal').modal('show');
            });

            // EDIT
            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                $.ajax({
                    url: `/${panel}/page-categories/${id}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            resetForm();
                            const cat = response.data;
                            $('#category_id').val(cat.category_id);
                            $('#category_name').val(cat.category_name);
                            $('#description').val(cat.description);
                            $('#status').val(cat.status);
                            $('#modalTitle').html(
                                '<i class="bi bi-pencil-square me-2"></i>Edit: ' + cat
                                .category_name);
                            $('#submitText').text('Update Category');
                            $('#form_action').val('update');
                            $('#categoryModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        if (typeof showError === 'function') showError(xhr.responseJSON
                            ?.message || 'Failed to load category.');
                        else Swal.fire('Error', xhr.responseJSON?.message ||
                            'Failed to load category.', 'error');
                    }
                });
            });

            // VIEW
            $(document).on('click', '.view-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                $.ajax({
                    url: `/${panel}/page-categories/${id}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const cat = response.data;
                            $('#viewCategoryName').text(cat.category_name);
                            $('#viewCategoryId').text('#' + cat.category_id);
                            $('#viewCategoryPages').html(
                                '<i class="bi bi-file-earmark me-1"></i>' + cat
                                .pages_count + ' page(s)');
                            $('#viewCategoryDesc').text(cat.description || 'No description');
                            $('#viewCategoryCreated').text(cat.created_at);
                            $('#viewCategoryUpdated').text(cat.updated_at);
                            $('#viewCategoryStatus').text(cat.status == 1 ? 'Active' :
                                'Inactive');
                            $('#viewCategoryModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        if (typeof showError === 'function') showError(xhr.responseJSON
                            ?.message || 'Failed to load category.');
                        else Swal.fire('Error', xhr.responseJSON?.message ||
                            'Failed to load category.', 'error');
                    }
                });
            });

            // SUBMIT
            $('#categoryForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-msg').text('');

                const action = $('#form_action').val();
                const id = $('#category_id').val();
                let url = `/${panel}/page-categories/store`;
                const formData = new FormData(this);

                if (action === 'update') {
                    url = `/${panel}/page-categories/${id}/update`;
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
                            $('#categoryModal').modal('hide');
                            if (typeof showToast === 'function') showToast('success', response
                                .message);
                            else Swal.fire('Success', response.message, 'success');
                            loadCategories(currentPage);
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
                            else Swal.fire('Error', xhr.responseJSON?.message || 'Error!',
                                'error');
                        }
                    },
                    complete: function() {
                        $('#submitBtn').prop('disabled', false).html(
                            '<i class="bi bi-check-lg me-1"></i> <span id="submitText">' +
                            (action === 'create' ? 'Create Category' : 'Update Category') +
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
                const pages = $(this).data('pages');

                if (pages > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cannot Delete!',
                        html: `Category <strong>"${name}"</strong> has <strong>${pages}</strong> page(s).<br>Delete or move them first.`,
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                const doDelete = function() {
                    $.ajax({
                        url: `/${panel}/page-categories/${id}/delete`,
                        method: 'DELETE',
                        success: function(response) {
                            if (response.success) {
                                if (typeof showToast === 'function') showToast('success',
                                    response.message);
                                loadCategories(currentPage);
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
                    confirmAction('Delete Category?', `Delete "${name}"? This cannot be undone.`,
                            'Yes, Delete!')
                        .then((r) => {
                            if (r.isConfirmed) doDelete();
                        });
                } else {
                    Swal.fire({
                        title: 'Delete Category?',
                        text: `Delete "${name}"? This cannot be undone.`,
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
                        url: `/${panel}/page-categories/${id}/toggle-status`,
                        method: 'PATCH',
                        success: function(response) {
                            if (response.success) {
                                if (typeof showToast === 'function') showToast('success',
                                    response.message);
                                loadCategories(currentPage);
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
                    confirmAction('Change Status?', 'Change status of this category?', 'Yes!')
                        .then((r) => {
                            if (r.isConfirmed) doToggle();
                        });
                } else {
                    Swal.fire({
                        title: 'Change Status?',
                        text: 'Change status of this category?',
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
                $('#categoryForm')[0].reset();
                $('#category_id').val('');
                $('.error-msg').text('');
            }

            loadCategories();
        });
    </script>
@endpush
