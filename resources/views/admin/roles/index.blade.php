@extends('layouts.admin')

@section('title', 'Roles')
@section('page-title', 'Role Management')

@section('content')

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-weight: 700; margin-bottom: 5px; color: #1a1d29;">All Roles</h4>
            <p style="color: #6b7280; margin-bottom: 0;">Manage system roles and their access levels</p>
        </div>
        <button type="button" class="btn" id="btnAddRole"
                style="background: var(--primary-color); color: #fff; border-radius: 8px; padding: 10px 20px;">
            <i class="bi bi-plus-lg me-1"></i> Add New Role
        </button>
    </div>

    <!-- Filters -->
    <div class="custom-table mb-4">
        <div style="padding: 20px 25px;">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" id="searchInput" class="form-control"
                           placeholder="🔍 Search by role name or description...">
                </div>
                <div class="col-md-2">
                    <select id="statusFilter" class="form-select">
                        <option value="all">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" id="resetFilters" class="btn btn-light w-100"
                            style="border-radius: 8px;">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles Table -->
    <div class="custom-table">
        <div class="table-header d-flex justify-content-between align-items-center">
            <div>
                <h6 style="font-weight: 600; margin-bottom: 2px;">Roles List</h6>
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
                        <th>Role Name</th>
                        <th>Description</th>
                        <th>Users</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="rolesTableBody">
                    <tr>
                        <td colspan="7" class="table-loading">
                            <div class="spinner-custom"></div>
                            <p class="text-muted mt-3">Loading roles...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
             style="border-top: 1px solid #f0f0f0; display:none;" id="paginationContainer">
            <small style="color: #6b7280;" id="paginationInfo"></small>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="paginationLinks"></ul>
            </nav>
        </div>
    </div>

    <!-- ================================================== -->
    <!-- ADD/EDIT ROLE MODAL -->
    <!-- ================================================== -->
    <div class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="bi bi-shield-plus me-2"></i>Add New Role
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="roleForm">
                    @csrf
                    <input type="hidden" id="role_id" name="role_id">
                    <input type="hidden" id="form_action" value="create">

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label" style="font-weight: 500;">
                                    Role Name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                    <input type="text" name="role_name" id="role_name"
                                           class="form-control" placeholder="e.g. Manager, Chef, Waiter"
                                           maxlength="45" required>
                                </div>
                                <small class="text-danger error-msg" data-field="role_name"></small>
                                <small class="text-muted">Max 45 characters</small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" style="font-weight: 500;">
                                    Description
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                    <textarea name="description" id="description"
                                              class="form-control" rows="3"
                                              placeholder="Describe what this role can do..."
                                              maxlength="255"></textarea>
                                </div>
                                <small class="text-danger error-msg" data-field="description"></small>
                                <small class="text-muted">Max 255 characters</small>
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

                        <!-- Info Alert -->
                        <div class="mt-3" style="padding: 15px; background: #eff6ff; border-radius: 10px; border-left: 4px solid #3b82f6;">
                            <small style="color: #1e40af;">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Note:</strong> After creating a role, you'll need to assign permissions from the Permissions page.
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                style="border-radius: 8px; padding: 8px 20px;">Cancel</button>
                        <button type="submit" class="btn" id="submitBtn"
                                style="background: var(--primary-color); color: #fff;
                                       border-radius: 8px; padding: 8px 20px;">
                            <i class="bi bi-check-lg me-1"></i> <span id="submitText">Create Role</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================================================== -->
    <!-- VIEW ROLE MODAL -->
    <!-- ================================================== -->
    <div class="modal fade" id="viewRoleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div style="background: linear-gradient(135deg, #4f46e5, #6366f1); padding: 40px; color: #fff; text-align: center; border-radius: 15px 15px 0 0;">
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                                data-bs-dismiss="modal"></button>
                        <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.2);
                                    border-radius: 20px; display: flex; align-items: center;
                                    justify-content: center; margin: 0 auto 15px; font-size: 2.5rem;">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h4 style="font-weight: 700; margin-bottom: 5px;" id="viewRoleName"></h4>
                        <span class="badge" id="viewRoleStatus"
                              style="background: rgba(255,255,255,0.2); padding: 6px 15px; border-radius: 20px;"></span>
                    </div>

                    <div style="padding: 30px;">
                        <h6 style="font-weight: 600; margin-bottom: 20px; color: #1a1d29;">
                            <i class="bi bi-info-circle me-2"></i>Role Information
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">ROLE ID</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewRoleId"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">ASSIGNED USERS</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewRoleUsers"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">DESCRIPTION</small>
                                    <div style="font-weight: 500; margin-top: 5px;" id="viewRoleDescription"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">CREATED AT</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewRoleCreated"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">LAST UPDATED</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewRoleUpdated"></div>
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

    // ==========================================
    // LOAD ROLES
    // ==========================================
    function loadRoles(page = 1) {
        currentPage = page;

        $('#rolesTableBody').html(`
            <tr>
                <td colspan="7" class="table-loading">
                    <div class="spinner-custom"></div>
                    <p class="text-muted mt-3">Loading roles...</p>
                </td>
            </tr>
        `);

        $.ajax({
            url: "{{ route('admin.roles.fetch') }}",
            method: 'GET',
            data: {
                page: page,
                search: $('#searchInput').val(),
                status: $('#statusFilter').val()
            },
            success: function(response) {
                renderRoles(response.data, response.pagination);
                renderPagination(response.pagination);
                $('#totalCount').text('Total: ' + response.pagination.total + ' roles');
            },
            error: function() {
                showError('Failed to load roles. Please try again.');
            }
        });
    }

    // ==========================================
    // RENDER ROLES
    // ==========================================
    function renderRoles(roles, pagination) {
        if (roles.length === 0) {
            $('#rolesTableBody').html(`
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-shield" style="font-size: 3rem; color: #d1d5db;"></i>
                        <p class="text-muted mt-2">No roles found</p>
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        let startNum = pagination.from;

        roles.forEach(function(role, index) {
            const statusBadge = role.status == 1
                ? `<span class="badge-active"><i class="bi bi-check-circle-fill me-1"></i>Active</span>`
                : `<span class="badge-inactive"><i class="bi bi-x-circle-fill me-1"></i>Inactive</span>`;

            const adminBadge = role.is_admin
                ? `<span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;
                        border-radius: 6px; padding: 3px 8px; font-size: 0.7rem; margin-left: 8px;">
                        <i class="bi bi-star-fill"></i> System
                   </span>`
                : '';

            let actionButtons = `
                <li><a class="dropdown-item view-btn" href="#" data-id="${role.role_id}">
                    <i class="bi bi-eye me-2"></i>View
                </a></li>
            `;

            if (!role.is_admin) {
                actionButtons += `
                    <li><a class="dropdown-item edit-btn" href="#" data-id="${role.role_id}">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </a></li>
                    <li><a class="dropdown-item toggle-btn" href="#" data-id="${role.role_id}">
                        ${role.status == 1
                            ? '<i class="bi bi-toggle-off me-2"></i>Deactivate'
                            : '<i class="bi bi-toggle-on me-2"></i>Activate'}
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger delete-btn" href="#"
                           data-id="${role.role_id}" data-name="${role.role_name}"
                           data-users="${role.users_count}">
                        <i class="bi bi-trash me-2"></i>Delete
                    </a></li>
                `;
            } else {
                actionButtons += `
                    <li><span class="dropdown-item text-muted">
                        <i class="bi bi-lock me-2"></i>Protected Role
                    </span></li>
                `;
            }

            html += `
                <tr>
                    <td>${startNum + index}</td>
                    <td>
                        <div style="font-weight: 600;">
                            <i class="bi bi-shield-lock-fill me-2 text-primary"></i>
                            ${role.role_name}
                            ${adminBadge}
                        </div>
                    </td>
                    <td>
                        <small style="color: #6b7280;">${role.description}</small>
                    </td>
                    <td>
                        <span class="badge"
                              style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;
                                     border-radius: 8px; padding: 6px 12px; font-weight: 600;">
                            <i class="bi bi-people me-1"></i> ${role.users_count}
                        </span>
                    </td>
                    <td>${statusBadge}</td>
                    <td>${role.created_at}</td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm" style="background: #f3f4f6; border-radius: 6px;"
                                    data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end"
                                style="border-radius: 10px; border: 1px solid #e5e7eb;
                                       box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                                ${actionButtons}
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
        });

        $('#rolesTableBody').html(html);
    }

    // ==========================================
    // RENDER PAGINATION
    // ==========================================
    function renderPagination(pagination) {
        if (pagination.last_page <= 1) {
            $('#paginationContainer').hide();
            return;
        }

        $('#paginationContainer').css('display', 'flex');
        $('#paginationInfo').text(`Showing ${pagination.from} to ${pagination.to} of ${pagination.total} entries`);

        let html = '';
        html += `<li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
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

    // ==========================================
    // PAGINATION CLICK
    // ==========================================
    $(document).on('click', '#paginationLinks .page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
            loadRoles(page);
        }
    });

    // ==========================================
    // SEARCH (Debounced)
    // ==========================================
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadRoles(1), 500);
    });

    $('#statusFilter').on('change', function() {
        loadRoles(1);
    });

    $('#resetFilters').on('click', function() {
        $('#searchInput').val('');
        $('#statusFilter').val('all');
        loadRoles(1);
    });

    $('#refreshBtn').on('click', function() {
        loadRoles(currentPage);
        showToast('success', 'Data refreshed!');
    });

    // ==========================================
    // ADD ROLE
    // ==========================================
    $('#btnAddRole').on('click', function() {
        resetForm();
        $('#modalTitle').html('<i class="bi bi-shield-plus me-2"></i>Add New Role');
        $('#submitText').text('Create Role');
        $('#form_action').val('create');
        $('#roleModal').modal('show');
    });

    // ==========================================
    // EDIT ROLE
    // ==========================================
    $(document).on('click', '.edit-btn', function(e) {
        e.preventDefault();
        const roleId = $(this).data('id');

        $.ajax({
            url: `/admin/roles/${roleId}/get`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    resetForm();
                    const role = response.data;
                    $('#role_id').val(role.role_id);
                    $('#role_name').val(role.role_name);
                    $('#description').val(role.description);
                    $('#status').val(role.status);

                    $('#modalTitle').html('<i class="bi bi-pencil-square me-2"></i>Edit Role: ' + role.role_name);
                    $('#submitText').text('Update Role');
                    $('#form_action').val('update');

                    $('#roleModal').modal('show');
                }
            },
            error: function() {
                showError('Failed to load role data.');
            }
        });
    });

    // ==========================================
    // VIEW ROLE
    // ==========================================
    $(document).on('click', '.view-btn', function(e) {
        e.preventDefault();
        const roleId = $(this).data('id');

        $.ajax({
            url: `/admin/roles/${roleId}/get`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const role = response.data;
                    $('#viewRoleName').text(role.role_name);
                    $('#viewRoleId').text('#' + role.role_id);
                    $('#viewRoleUsers').html('<i class="bi bi-people me-1"></i>' + role.users_count + ' user(s)');
                    $('#viewRoleDescription').text(role.description || 'No description provided');
                    $('#viewRoleCreated').text(role.created_at);
                    $('#viewRoleUpdated').text(role.updated_at);

                    $('#viewRoleStatus').text(role.status == 1 ? 'Active' : 'Inactive');
                    $('#viewRoleModal').modal('show');
                }
            },
            error: function() {
                showError('Failed to load role data.');
            }
        });
    });

    // ==========================================
    // SUBMIT FORM
    // ==========================================
    $('#roleForm').on('submit', function(e) {
        e.preventDefault();
        $('.error-msg').text('');

        const action = $('#form_action').val();
        const roleId = $('#role_id').val();

        let url = "{{ route('admin.roles.store') }}";
        let method = 'POST';

        const formData = new FormData(this);

        if (action === 'update') {
            url = `/admin/roles/${roleId}/update`;
            formData.append('_method', 'PUT');
        }

        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#roleModal').modal('hide');
                    showToast('success', response.message);
                    loadRoles(currentPage);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function(field) {
                        $(`.error-msg[data-field="${field}"]`).text(errors[field][0]);
                    });
                    showToast('error', 'Please fix the errors below');
                } else {
                    showError(xhr.responseJSON?.message || 'Something went wrong!');
                }
            },
            complete: function() {
                $('#submitBtn').prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> <span id="submitText">' +
                    (action === 'create' ? 'Create Role' : 'Update Role') + '</span>');
            }
        });
    });

    // ==========================================
    // DELETE ROLE
    // ==========================================
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        const roleId = $(this).data('id');
        const roleName = $(this).data('name');
        const usersCount = $(this).data('users');

        if (usersCount > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Cannot Delete!',
                html: `The role <strong>"${roleName}"</strong> has <strong>${usersCount}</strong> user(s) assigned to it.<br>Please reassign them before deleting.`,
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        confirmAction(
            'Delete Role?',
            `Are you sure you want to delete "${roleName}"? This action cannot be undone.`,
            'Yes, Delete!'
        ).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/roles/${roleId}/delete`,
                    method: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            loadRoles(currentPage);
                        }
                    },
                    error: function(xhr) {
                        showError(xhr.responseJSON?.message || 'Failed to delete role.');
                    }
                });
            }
        });
    });

    // ==========================================
    // TOGGLE STATUS
    // ==========================================
    $(document).on('click', '.toggle-btn', function(e) {
        e.preventDefault();
        const roleId = $(this).data('id');

        confirmAction(
            'Change Status?',
            'Are you sure you want to change this role\'s status?',
            'Yes, Change!'
        ).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/roles/${roleId}/toggle-status`,
                    method: 'PATCH',
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            loadRoles(currentPage);
                        }
                    },
                    error: function(xhr) {
                        showError(xhr.responseJSON?.message || 'Failed to update status.');
                    }
                });
            }
        });
    });

    // ==========================================
    // RESET FORM
    // ==========================================
    function resetForm() {
        $('#roleForm')[0].reset();
        $('#role_id').val('');
        $('.error-msg').text('');
    }

    // Initial load
    loadRoles();
});
</script>
@endpush
