@php
    $panel = $panel ?? (auth()->user()->role->role_name === 'Manager' ? 'manager' : 'admin');
@endphp
@extends($panel === 'manager' ? 'layouts.manager' : 'layouts.admin')

@section('title', 'Item Sub Categories')
@section('page-title', 'Item Sub Categories')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-weight:700;margin-bottom:5px;color:#1a1d29;">Item Sub Categories</h4>
            <p style="color:#6b7280;margin-bottom:0;">Sub categories under main item categories</p>
        </div>
        <button type="button" class="btn" id="btnAdd"
            style="background:var(--primary-color);color:#fff;border-radius:8px;padding:10px 20px;">
            <i class="bi bi-plus-lg me-1"></i> Add Sub Category
        </button>
    </div>

    <div class="custom-table mb-4">
        <div style="padding:20px 25px;">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control"
                        placeholder="🔍 Search by name or description...">
                </div>
                <div class="col-md-3">
                    <select id="categoryFilter" class="form-select">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="statusFilter" class="form-select">
                        <option value="all">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
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
        <div class="table-header d-flex justify-content-between align-items-center">
            <div>
                <h6 style="font-weight:600;margin-bottom:2px;">Sub Category List</h6>
                <small style="color:#6b7280;" id="totalCount">Loading...</small>
            </div>
            <button type="button" id="refreshBtn" class="btn btn-light btn-sm" style="border-radius:8px;">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Sub Category Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="7" class="table-loading">
                            <div class="spinner-custom"></div>
                            <p class="text-muted mt-3">Loading sub categories...</p>
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

    <!-- Modal -->
    <div class="modal fade" id="formModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"><i class="bi bi-diagram-2 me-2"></i>Add New Sub Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="dataForm">
                    @csrf
                    <input type="hidden" id="row_id" name="id">
                    <input type="hidden" id="form_action" value="create">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label" style="font-weight:500;">Item Category <span
                                        class="text-danger">*</span></label>
                                <select name="item_category_id" id="item_category_id" class="form-select" required>
                                    <option value="">Select...</option>
                                </select>
                                <small class="text-danger error-msg" data-field="item_category_id"></small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label" style="font-weight:500;">Sub Category Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" maxlength="100"
                                    required>
                                <small class="text-danger error-msg" data-field="name"></small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label" style="font-weight:500;">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label" style="font-weight:500;">Status <span
                                        class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius:8px;">Cancel</button>
                        <button type="submit" class="btn" id="submitBtn"
                            style="background:var(--primary-color);color:#fff;border-radius:8px;">
                            <i class="bi bi-check-lg me-1"></i> <span id="submitText">Save</span>
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
                canAdd: @json(auth()->user()->hasOptionPermission('ITEM_SUB_CAT_ADD')),
                canEdit: @json(auth()->user()->hasOptionPermission('ITEM_SUB_CAT_EDIT')),
                canDelete: @json(auth()->user()->hasOptionPermission('ITEM_SUB_CAT_DELETE')),
                canView: @json(auth()->user()->hasOptionPermission('ITEM_SUB_CAT_VIEW')),
            };
            if (!perms.canAdd) $('#btnAdd').hide();

            let currentPage = 1,
                searchTimeout;

            function loadCategories() {
                $.get(`/${panel}/item-categories/active`, function(res) {
                    if (!res.success) return;
                    let filterOpts = '<option value="">All Categories</option>';
                    let formOpts = '<option value="">Select...</option>';
                    res.data.forEach(c => {
                        filterOpts += `<option value="${c.id}">${c.name}</option>`;
                        formOpts += `<option value="${c.id}">${c.name}</option>`;
                    });
                    $('#categoryFilter').html(filterOpts);
                    $('#item_category_id').html(formOpts);
                });
            }

            function loadData(page = 1) {
                currentPage = page;
                $('#tableBody').html(
                    `<tr><td colspan="7" class="table-loading"><div class="spinner-custom"></div><p class="text-muted mt-3">Loading...</p></td></tr>`
                    );

                $.ajax({
                    url: `/${panel}/item-sub-categories/fetch`,
                    method: 'GET',
                    data: {
                        page,
                        search: $('#searchInput').val(),
                        item_category_id: $('#categoryFilter').val(),
                        status: $('#statusFilter').val()
                    },
                    success: function(res) {
                        renderTable(res.data, res.pagination);
                        renderPagination(res.pagination);
                        $('#totalCount').text('Total: ' + res.pagination.total + ' sub categories');
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || xhr.responseJSON?.error ||
                            'Failed to load sub categories.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }

            function renderTable(rows, pagination) {
                if (!rows.length) {
                    $('#tableBody').html(
                        `<tr><td colspan="7" class="text-center py-5 text-muted">No sub categories found</td></tr>`
                        );
                    return;
                }

                let html = '',
                    start = pagination.from;
                rows.forEach((r, i) => {
                    const status = r.status == 1 ?
                        `<span class="badge-active">Active</span>` :
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
                    if (!actions) actions =
                        `<li><span class="dropdown-item text-muted">No actions</span></li>`;

                    html += `<tr>
                <td>${start + i}</td>
                <td><span class="badge" style="background:#eef2ff;color:#4f46e5;">${r.category_name}</span></td>
                <td style="font-weight:600;">${r.name}</td>
                <td><small class="text-muted">${r.description ?? '-'}</small></td>
                <td>${status}</td>
                <td>${r.created_at}</td>
                <td class="text-end">
                    <div class="dropdown">
                        <button class="btn btn-sm" style="background:#f3f4f6;border-radius:6px;" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">${actions}</ul>
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
            $('#categoryFilter, #statusFilter').on('change', () => loadData(1));
            $('#resetFilters').on('click', function() {
                $('#searchInput').val('');
                $('#categoryFilter').val('');
                $('#statusFilter').val('all');
                loadData(1);
            });
            $('#refreshBtn').on('click', () => loadData(currentPage));

            $('#btnAdd').on('click', function() {
                resetForm();
                $('#modalTitle').html('<i class="bi bi-diagram-2 me-2"></i>Add New Sub Category');
                $('#submitText').text('Save');
                $('#form_action').val('create');
                $('#formModal').modal('show');
            });

            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                $.get(`/${panel}/item-sub-categories/${id}/get`, function(res) {
                    if (!res.success) return;
                    resetForm();
                    const r = res.data;
                    $('#row_id').val(r.id);
                    $('#item_category_id').val(r.item_category_id);
                    $('#name').val(r.name);
                    $('#description').val(r.description);
                    $('#status').val(r.status);
                    $('#modalTitle').html(
                        '<i class="bi bi-pencil-square me-2"></i>Edit Sub Category');
                    $('#submitText').text('Update');
                    $('#form_action').val('update');
                    $('#formModal').modal('show');
                });
            });

            $(document).on('click', '.view-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                $.get(`/${panel}/item-sub-categories/${id}/get`, function(res) {
                    if (!res.success) return;
                    const r = res.data;
                    Swal.fire({
                        title: r.name,
                        html: `<div style="text-align:left">
                    <p><b>Category:</b> ${r.category_name}</p>
                    <p><b>Description:</b> ${r.description || '-'}</p>
                    <p><b>Status:</b> ${r.status == 1 ? 'Active' : 'Inactive'}</p>
                    <p><b>Created:</b> ${r.created_at}</p>
                </div>`
                    });
                });
            });

            $('#dataForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-msg').text('');
                const action = $('#form_action').val();
                const id = $('#row_id').val();
                let url = `/${panel}/item-sub-categories/store`;
                const fd = new FormData(this);
                if (action === 'update') {
                    url = `/${panel}/item-sub-categories/${id}/update`;
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
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors || {};
                            Object.keys(errors).forEach(f => $(`.error-msg[data-field="${f}"]`)
                                .text(errors[f][0]));
                        } else {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed', 'error');
                        }
                    },
                    complete: function() {
                        $('#submitBtn').prop('disabled', false).html(
                            '<i class="bi bi-check-lg me-1"></i> <span id="submitText">Save</span>'
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
                    text: `Delete sub category "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444'
                }).then(r => {
                    if (!r.isConfirmed) return;
                    $.ajax({
                        url: `/${panel}/item-sub-categories/${id}/delete`,
                        method: 'DELETE',
                        success: function(res) {
                            if (res.success) {
                                if (typeof showToast === 'function') showToast(
                                    'success', res.message);
                                loadData(currentPage);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed',
                                'error');
                        }
                    });
                });
            });

            $(document).on('click', '.toggle-btn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                $.ajax({
                    url: `/${panel}/item-sub-categories/${id}/toggle-status`,
                    method: 'PATCH',
                    success: function(res) {
                        if (res.success) loadData(currentPage);
                    }
                });
            });

            function resetForm() {
                $('#dataForm')[0].reset();
                $('#row_id').val('');
                $('.error-msg').text('');
                $('#status').val('1');
            }

            loadCategories();
            loadData();
        });
    </script>
@endpush
