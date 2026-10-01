@php
    $panel = $panel ?? (auth()->user()->role->role_name === 'Manager' ? 'manager' : 'admin');
@endphp
@extends($panel === 'manager' ? 'layouts.manager' : 'layouts.admin')

@section('title', 'Menu Categories')
@section('page-title', 'Menu Categories')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-weight: 700; margin-bottom: 5px; color: #1a1d29;">Menu Categories</h4>
            <p style="color: #6b7280; margin-bottom: 0;">Organize your restaurant menu (e.g. Fried Rice, Noodles)</p>
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
                    <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search by name...">
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
                        <th>Total Items</th>
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
                        <i class="bi bi-grid me-2"></i>Add New Category
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
                                <label class="form-label" style="font-weight: 500;">Category Name <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                    <input type="text" name="category_name" id="category_name" class="form-control"
                                        placeholder="e.g. Fried Rice, Noodles" maxlength="100" required>
                                </div>
                                <small class="text-danger error-msg" data-field="category_name"></small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" style="font-weight: 500;">Description</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <textarea name="description" id="description" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                                </div>
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
                            style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 2.5rem;">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </div>
                        <h4 style="font-weight: 700; margin-bottom: 5px;" id="viewCategoryName"></h4>
                    </div>

                    <div style="padding: 30px;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">ITEMS COUNT</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewCategoryItems"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">STATUS</small>
                                    <div style="margin-top: 5px;" id="viewCategoryStatus"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">DESCRIPTION</small>
                                    <div style="font-weight: 500; margin-top: 5px;" id="viewCategoryDesc"></div>
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
                canAdd: @json(auth()->user()->hasOptionPermission('MENU_CAT_ADD')),
                canEdit: @json(auth()->user()->hasOptionPermission('MENU_CAT_EDIT')),
                canDelete: @json(auth()->user()->hasOptionPermission('MENU_CAT_DELETE')),
                canView: @json(auth()->user()->hasOptionPermission('MENU_CAT_VIEW')),
            };

            if (!userPermissions.canAdd) {
                $('#btnAddCategory').hide();
            }

            let currentPage = 1;
            let searchTimeout;

            function loadCategories(page = 1) {
                currentPage = page;
                $('#categoriesTableBody').html(
                    `<tr><td colspan="7" class="table-loading"><div class="spinner-custom"></div><p class="text-muted mt-3">Loading...</p></td></tr>`
                    );

                $.ajax({
                    url: `/${panel}/menu-categories/fetch`,
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
                    }
                });
            }

            function renderCategories(categories, pagination) {
                if (categories.length === 0) {
                    $('#categoriesTableBody').html(
                        `<tr><td colspan="7" class="text-center py-5"><i class="bi bi-inbox" style="font-size: 3rem; color: #d1d5db;"></i><p class="text-muted mt-2">No categories found</p></td></tr>`
                        );
                    return;
                }

                let html = '';
                let startNum = pagination.from;

                categories.forEach(function(cat, index) {
                    const statusBadge = cat.status == 1 ? `<span class="badge-active">Active</span>` :
                        `<span class="badge-inactive">Inactive</span>`;

                    let actionButtons = '';
                    if (userPermissions.canView) actionButtons +=
                        `<li><a class="dropdown-item view-btn" href="#" data-id="${cat.category_id}"><i class="bi bi-eye me-2"></i>View</a></li>`;
                    if (userPermissions.canEdit) {
                        actionButtons +=
                            `<li><a class="dropdown-item edit-btn" href="#" data-id="${cat.category_id}"><i class="bi bi-pencil me-2"></i>Edit</a></li>`;
                        actionButtons +=
                            `<li><a class="dropdown-item toggle-btn" href="#" data-id="${cat.category_id}">${cat.status == 1 ? '<i class="bi bi-toggle-off me-2"></i>Deactivate' : '<i class="bi bi-toggle-on me-2"></i>Activate'}</a></li>`;
                    }
                    if (userPermissions.canDelete) {
                        actionButtons +=
                            `<li><hr class="dropdown-divider"></li><li><a class="dropdown-item text-danger delete-btn" href="#" data-id="${cat.category_id}" data-name="${cat.category_name}"><i class="bi bi-trash me-2"></i>Delete</a></li>`;
                    }
                    if (actionButtons === '') actionButtons =
                        `<li><span class="dropdown-item text-muted">No actions allowed</span></li>`;

                    html += `
                <tr>
                    <td>${startNum + index}</td>
                    <td>
                        <div style="font-weight: 600; color: var(--primary-color);">
                            <i class="bi bi-grid-3x3-gap me-2"></i>${cat.category_name}
                        </div>
                    </td>
                    <td><small style="color: #6b7280;">${cat.description}</small></td>
                    <td><span class="badge" style="background: #f3f4f6; color: #374151; padding: 5px 10px;">${cat.items_count} items</span></td>
                    <td>${statusBadge}</td>
                    <td>${cat.created_at}</td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm" style="background:#f3f4f6; border-radius:6px;" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end" style="border-radius:10px; border:1px solid #e5e7eb;">${actionButtons}</ul>
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
                    loadCategories(page);
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
                $('#modalTitle').html('<i class="bi bi-grid-plus me-2"></i>Add New Category');
                $('#submitText').text('Create Category');
                $('#form_action').val('create');
                $('#categoryModal').modal('show');
            });

            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                $.ajax({
                    url: `/${panel}/menu-categories/${id}/get`,
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
                    }
                });
            });

            $(document).on('click', '.view-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                $.ajax({
                    url: `/${panel}/menu-categories/${id}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const cat = response.data;
                            $('#viewCategoryName').text(cat.category_name);
                            $('#viewCategoryItems').text(cat.items_count + ' items');
                            $('#viewCategoryDesc').text(cat.description || 'No description');
                            $('#viewCategoryStatus').html(cat.status == 1 ?
                                '<span class="badge-active">Active</span>' :
                                '<span class="badge-inactive">Inactive</span>');
                            $('#viewCategoryModal').modal('show');
                        }
                    }
                });
            });

            $('#categoryForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-msg').text('');
                const action = $('#form_action').val();
                const id = $('#category_id').val();
                let url = `/${panel}/menu-categories/store`;
                const formData = new FormData(this);
                if (action === 'update') {
                    url = `/${panel}/menu-categories/${id}/update`;
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
                            loadCategories(currentPage);
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
                                action === 'create' ? 'Create Category' : 'Update Category'
                                ) + '</span>');
                    }
                });
            });

            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const name = $(this).data('name');
                const doDel = function() {
                    $.ajax({
                        url: `/${panel}/menu-categories/${id}/delete`,
                        method: 'DELETE',
                        success: function(r) {
                            if (r.success) {
                                if (typeof showToast === 'function') showToast('success', r
                                    .message);
                                loadCategories(currentPage);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed!',
                                'error');
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
                    url: `/${panel}/menu-categories/${id}/toggle-status`,
                    method: 'PATCH',
                    success: function(r) {
                        if (r.success) {
                            loadCategories(currentPage);
                        }
                    }
                });
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
