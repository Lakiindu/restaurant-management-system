@extends('layouts.admin')

@section('title', 'Pages')
@section('page-title', 'Pages Management')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-weight: 700; margin-bottom: 5px; color: #1a1d29;">System Pages</h4>
            <p style="color: #6b7280; margin-bottom: 0;">Manage system pages that can be assigned to roles</p>
        </div>
        <button type="button" class="btn" id="btnAddPage"
            style="background: var(--primary-color); color: #fff; border-radius: 8px; padding: 10px 20px;">
            <i class="bi bi-plus-lg me-1"></i> Add New Page
        </button>
    </div>

    <div class="custom-table mb-4">
        <div style="padding: 20px 25px;">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" id="searchInput" class="form-control"
                        placeholder="🔍 Search by name, code, or route...">
                </div>
                <div class="col-md-3">
                    <select id="categoryFilter" class="form-select">
                        <option value="">All Categories</option>
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
                <h6 style="font-weight: 600; margin-bottom: 2px;">Pages List</h6>
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
                        <th>Page Name</th>
                        <th>Code</th>
                        <th>Category</th>
                        <th>Options</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="pagesTableBody">
                    <tr>
                        <td colspan="7" class="table-loading">
                            <div class="spinner-custom"></div>
                            <p class="text-muted mt-3">Loading pages...</p>
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
    <div class="modal fade" id="pageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="bi bi-file-earmark-plus me-2"></i>Add New Page
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="pageForm">
                    @csrf
                    <input type="hidden" id="page_id" name="page_id">
                    <input type="hidden" id="form_action" value="create">

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">
                                    Page Name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-file-earmark"></i></span>
                                    <input type="text" name="page_name" id="page_name" class="form-control"
                                        placeholder="e.g. Menu Items" maxlength="45" required>
                                </div>
                                <small class="text-danger error-msg" data-field="page_name"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">
                                    Page Code <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-code"></i></span>
                                    <input type="text" name="page_code" id="page_code" class="form-control"
                                        placeholder="e.g. MENU_LIST" style="text-transform: uppercase;" required>
                                </div>
                                <small class="text-danger error-msg" data-field="page_code"></small>
                                <small class="text-muted">UPPERCASE only, use underscores</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-folder"></i></span>
                                    <select name="category_id" id="category_id" class="form-select" required>
                                        <option value="">-- Select Category --</option>
                                    </select>
                                </div>
                                <small class="text-danger error-msg" data-field="category_id"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">Route Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                    <input type="text" name="route_name" id="route_name" class="form-control"
                                        placeholder="e.g. admin.menu.index">
                                </div>
                                <small class="text-muted">Laravel route name (optional)</small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" style="font-weight: 500;">Description</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <textarea name="description" id="description" class="form-control" rows="2"
                                        placeholder="What does this page do?" maxlength="255"></textarea>
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
                            style="background: var(--primary-color); color: #fff;
                                       border-radius: 8px; padding: 8px 20px;">
                            <i class="bi bi-check-lg me-1"></i> <span id="submitText">Create Page</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- VIEW MODAL -->
    <div class="modal fade" id="viewPageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div
                        style="background: linear-gradient(135deg, #4f46e5, #6366f1); padding: 40px; color: #fff; text-align: center; border-radius: 15px 15px 0 0;">
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                            data-bs-dismiss="modal"></button>
                        <div
                            style="width: 80px; height: 80px; background: rgba(255,255,255,0.2);
                                    border-radius: 20px; display: flex; align-items: center;
                                    justify-content: center; margin: 0 auto 15px; font-size: 2.5rem;">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                        <h4 style="font-weight: 700; margin-bottom: 5px;" id="viewPageName"></h4>
                        <code
                            style="background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 6px; color: #fff;"
                            id="viewPageCode"></code>
                    </div>

                    <div style="padding: 30px;">
                        <h6 style="font-weight: 600; margin-bottom: 20px; color: #1a1d29;">
                            <i class="bi bi-info-circle me-2"></i>Page Information
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">CATEGORY</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewPageCategory"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">STATUS</small>
                                    <div style="margin-top: 5px;" id="viewPageStatus"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">ROUTE NAME</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewPageRoute"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">OPTIONS COUNT</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewPageOptions"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">DESCRIPTION</small>
                                    <div style="font-weight: 500; margin-top: 5px;" id="viewPageDesc"></div>
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
            let currentPage = 1;
            let searchTimeout;

            function loadCategories() {
                $.ajax({
                    url: "{{ route('admin.page-categories.active') }}",
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            let filterOpts = '<option value="">All Categories</option>';
                            let formOpts = '<option value="">-- Select Category --</option>';
                            response.data.forEach(function(cat) {
                                filterOpts +=
                                    `<option value="${cat.category_id}">${cat.category_name}</option>`;
                                formOpts +=
                                    `<option value="${cat.category_id}">${cat.category_name}</option>`;
                            });
                            $('#categoryFilter').html(filterOpts);
                            $('#category_id').html(formOpts);
                        }
                    }
                });
            }

            function loadPages(page = 1) {
                currentPage = page;
                $('#pagesTableBody').html(`
            <tr><td colspan="7" class="table-loading">
                <div class="spinner-custom"></div>
                <p class="text-muted mt-3">Loading pages...</p>
            </td></tr>
        `);

                $.ajax({
                    url: "{{ route('admin.pages.fetch') }}",
                    method: 'GET',
                    data: {
                        page: page,
                        search: $('#searchInput').val(),
                        category_id: $('#categoryFilter').val(),
                        status: $('#statusFilter').val()
                    },
                    success: function(response) {
                        renderPages(response.data, response.pagination);
                        renderPagination(response.pagination);
                        $('#totalCount').text('Total: ' + response.pagination.total + ' pages');
                    }
                });
            }

            function renderPages(pages, pagination) {
                if (pages.length === 0) {
                    $('#pagesTableBody').html(`
                <tr><td colspan="7" class="text-center py-5">
                    <i class="bi bi-file-earmark-x" style="font-size: 3rem; color: #d1d5db;"></i>
                    <p class="text-muted mt-2">No pages found</p>
                </td></tr>
            `);
                    return;
                }

                let html = '';
                let startNum = pagination.from;

                pages.forEach(function(p, index) {
                    const statusBadge = p.status == 1 ?
                        `<span class="badge-active"><i class="bi bi-check-circle-fill me-1"></i>Active</span>` :
                        `<span class="badge-inactive"><i class="bi bi-x-circle-fill me-1"></i>Inactive</span>`;

                    html += `
                <tr>
                    <td>${startNum + index}</td>
                    <td>
                        <div style="font-weight: 600;">
                            <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                            ${p.page_name}
                        </div>
                        <small style="color: #6b7280;">${p.description}</small>
                    </td>
                    <td>
                        <code style="background: #f3f4f6; padding: 3px 8px; border-radius: 4px; color: #4f46e5; font-size: 0.8rem;">
                            ${p.page_code}
                        </code>
                    </td>
                    <td>
                        <span class="badge"
                              style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;
                                     border-radius: 8px; padding: 6px 12px;">
                            <i class="bi bi-folder-fill me-1"></i>${p.category_name}
                        </span>
                    </td>
                    <td>
                        <span class="badge"
                              style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;
                                     border-radius: 8px; padding: 6px 12px; font-weight: 600;">
                            <i class="bi bi-lightning me-1"></i>${p.options_count}
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
                                <li><a class="dropdown-item view-btn" href="#" data-id="${p.page_id}">
                                    <i class="bi bi-eye me-2"></i>View
                                </a></li>
                                <li><a class="dropdown-item edit-btn" href="#" data-id="${p.page_id}">
                                    <i class="bi bi-pencil me-2"></i>Edit
                                </a></li>
                                <li><a class="dropdown-item toggle-btn" href="#" data-id="${p.page_id}">
                                    ${p.status == 1
                                        ? '<i class="bi bi-toggle-off me-2"></i>Deactivate'
                                        : '<i class="bi bi-toggle-on me-2"></i>Activate'}
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger delete-btn" href="#"
                                       data-id="${p.page_id}" data-name="${p.page_name}"
                                       data-options="${p.options_count}">
                                    <i class="bi bi-trash me-2"></i>Delete
                                </a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
                });

                $('#pagesTableBody').html(html);
            }

            function renderPagination(pagination) {
                if (pagination.last_page <= 1) {
                    $('#paginationContainer').hide();
                    return;
                }
                $('#paginationContainer').css('display', 'flex');
                $('#paginationInfo').text(
                    `Showing ${pagination.from} to ${pagination.to} of ${pagination.total} entries`);

                let html = `<li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
                    </li>`;
                for (let i = 1; i <= pagination.last_page; i++) {
                    html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                     </li>`;
                }
                html += `<li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a>
                 </li>`;
                $('#paginationLinks').html(html);
            }

            $(document).on('click', '#paginationLinks .page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass(
                        'active')) {
                    loadPages(page);
                }
            });

            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => loadPages(1), 500);
            });

            $('#categoryFilter, #statusFilter').on('change', () => loadPages(1));

            $('#resetFilters').on('click', function() {
                $('#searchInput').val('');
                $('#categoryFilter').val('');
                $('#statusFilter').val('all');
                loadPages(1);
            });

            $('#refreshBtn').on('click', function() {
                loadPages(currentPage);
                showToast('success', 'Refreshed!');
            });

            // Auto uppercase page_code
            $('#page_code').on('input', function() {
                $(this).val($(this).val().toUpperCase().replace(/[^A-Z0-9_]/g, ''));
            });

            $('#btnAddPage').on('click', function() {
                resetForm();
                $('#modalTitle').html('<i class="bi bi-file-earmark-plus me-2"></i>Add New Page');
                $('#submitText').text('Create Page');
                $('#form_action').val('create');
                $('#page_code').prop('readonly', false);
                $('#pageModal').modal('show');
            });

            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                $.ajax({
                    url: `/admin/pages/${id}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            resetForm();
                            const p = response.data;
                            $('#page_id').val(p.page_id);
                            $('#page_name').val(p.page_name);
                            $('#page_code').val(p.page_code).prop('readonly', true);
                            $('#route_name').val(p.route_name);
                            $('#description').val(p.description);
                            setTimeout(() => $('#category_id').val(p.category_id), 100);
                            $('#status').val(p.status);
                            $('#modalTitle').html(
                                '<i class="bi bi-pencil-square me-2"></i>Edit: ' + p
                                .page_name);
                            $('#submitText').text('Update Page');
                            $('#form_action').val('update');
                            $('#pageModal').modal('show');
                        }
                    }
                });
            });

            $(document).on('click', '.view-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                $.ajax({
                    url: `/admin/pages/${id}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const p = response.data;
                            $('#viewPageName').text(p.page_name);
                            $('#viewPageCode').text(p.page_code);
                            $('#viewPageCategory').html(
                                '<i class="bi bi-folder-fill text-warning me-1"></i>' + p
                                .category_name);
                            $('#viewPageRoute').text(p.route_name || '-');
                            $('#viewPageOptions').html('<i class="bi bi-lightning me-1"></i>' +
                                p.options_count + ' option(s)');
                            $('#viewPageDesc').text(p.description || 'No description');
                            $('#viewPageStatus').html(p.status == 1 ?
                                '<span class="badge-active">Active</span>' :
                                '<span class="badge-inactive">Inactive</span>');
                            $('#viewPageModal').modal('show');
                        }
                    }
                });
            });

            $('#pageForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-msg').text('');

                const action = $('#form_action').val();
                const id = $('#page_id').val();
                let url = "{{ route('admin.pages.store') }}";
                const formData = new FormData(this);

                if (action === 'update') {
                    url = `/admin/pages/${id}/update`;
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
                            $('#pageModal').modal('hide');
                            showToast('success', response.message);
                            loadPages(currentPage);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(f => $(`.error-msg[data-field="${f}"]`)
                                .text(errors[f][0]));
                            showToast('error', 'Please fix errors');
                        } else {
                            showError(xhr.responseJSON?.message || 'Error!');
                        }
                    },
                    complete: function() {
                        $('#submitBtn').prop('disabled', false).html(
                            '<i class="bi bi-check-lg me-1"></i> <span id="submitText">' +
                            (action === 'create' ? 'Create Page' : 'Update Page') +
                            '</span>');
                    }
                });
            });

            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const name = $(this).data('name');
                const options = $(this).data('options');

                if (options > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cannot Delete!',
                        html: `Page <strong>"${name}"</strong> has <strong>${options}</strong> option(s).<br>Delete them first.`,
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                confirmAction('Delete Page?', `Delete "${name}"?`, 'Yes, Delete!').then((r) => {
                    if (r.isConfirmed) {
                        $.ajax({
                            url: `/admin/pages/${id}/delete`,
                            method: 'DELETE',
                            success: function(response) {
                                if (response.success) {
                                    showToast('success', response.message);
                                    loadPages(currentPage);
                                }
                            },
                            error: function(xhr) {
                                showError(xhr.responseJSON?.message || 'Failed!');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.toggle-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                confirmAction('Change Status?', 'Change status?', 'Yes!').then((r) => {
                    if (r.isConfirmed) {
                        $.ajax({
                            url: `/admin/pages/${id}/toggle-status`,
                            method: 'PATCH',
                            success: function(response) {
                                if (response.success) {
                                    showToast('success', response.message);
                                    loadPages(currentPage);
                                }
                            }
                        });
                    }
                });
            });

            function resetForm() {
                $('#pageForm')[0].reset();
                $('#page_id').val('');
                $('.error-msg').text('');
            }

            loadCategories();
            loadPages();
        });
    </script>
@endpush
