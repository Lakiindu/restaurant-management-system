@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-weight: 700; margin-bottom: 5px; color: #1a1d29;">All Users</h4>
            <p style="color: #6b7280; margin-bottom: 0;">Manage system users and their access</p>
        </div>
        <button type="button" class="btn" id="btnAddUser"
                style="background: var(--primary-color); color: #fff; border-radius: 8px; padding: 10px 20px;">
            <i class="bi bi-plus-lg me-1"></i> Add New User
        </button>
    </div>

    <!-- Filters -->
    <div class="custom-table mb-4">
        <div style="padding: 20px 25px;">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" id="searchInput" class="form-control"
                           placeholder="🔍 Search by username or email...">
                </div>
                <div class="col-md-3">
                    <select id="roleFilter" class="form-select">
                        <option value="">All Roles</option>
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
                    <button type="button" id="resetFilters" class="btn btn-light w-100"
                            style="border-radius: 8px;">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="custom-table">
        <div class="table-header d-flex justify-content-between align-items-center">
            <div>
                <h6 style="font-weight: 600; margin-bottom: 2px;">Users List</h6>
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
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr>
                        <td colspan="7" class="table-loading">
                            <div class="spinner-custom"></div>
                            <p class="text-muted mt-3">Loading users...</p>
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
    <!-- ADD/EDIT USER MODAL -->
    <!-- ================================================== -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="bi bi-person-plus me-2"></i>Add New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="userForm">
                    @csrf
                    <input type="hidden" id="user_id" name="user_id">
                    <input type="hidden" id="form_action" value="create">

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">
                                    Username <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="user_name" id="user_name"
                                           class="form-control" placeholder="e.g. john_doe" required>
                                </div>
                                <small class="text-danger error-msg" data-field="user_name"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="email"
                                           class="form-control" placeholder="user@restaurant.com" required>
                                </div>
                                <small class="text-danger error-msg" data-field="email"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">
                                    Password <span class="text-danger" id="passwordRequired">*</span>
                                    <small class="text-muted d-none" id="passwordHint">(Leave blank to keep current)</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="password"
                                           class="form-control" placeholder="Min 6 characters">
                                </div>
                                <small class="text-danger error-msg" data-field="password"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">
                                    Confirm Password <span class="text-danger" id="passwordConfirmRequired">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                           class="form-control" placeholder="Re-enter password">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">
                                    Role <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                    <select name="role_id" id="role_id" class="form-select" required>
                                        <option value="">-- Select Role --</option>
                                    </select>
                                </div>
                                <small class="text-danger error-msg" data-field="role_id"></small>
                            </div>

                            <div class="col-md-6">
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

                            <div class="col-12">
                                <div class="form-check" style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <input type="checkbox" name="must_change_password" value="1"
                                           class="form-check-input" id="must_change_password">
                                    <label class="form-check-label" for="must_change_password" style="font-weight: 500;">
                                        <i class="bi bi-exclamation-triangle me-1 text-warning"></i>
                                        Force user to change password on next login
                                    </label>
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
                            <i class="bi bi-check-lg me-1"></i> <span id="submitText">Create User</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================================================== -->
    <!-- VIEW USER MODAL -->
    <!-- ================================================== -->
    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div style="background: linear-gradient(135deg, #4f46e5, #6366f1); padding: 40px; color: #fff; text-align: center; border-radius: 15px 15px 0 0;">
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                                data-bs-dismiss="modal"></button>
                        <div class="user-avatar mx-auto mb-3"
                             style="width: 90px; height: 90px; font-size: 2.5rem; background: rgba(255,255,255,0.2);"
                             id="viewUserInitial"></div>
                        <h4 style="font-weight: 700; margin-bottom: 5px;" id="viewUserName"></h4>
                        <p style="opacity: 0.9; margin-bottom: 10px;" id="viewUserEmail"></p>
                        <span class="badge" id="viewUserRole"
                              style="background: rgba(255,255,255,0.2); padding: 6px 15px; border-radius: 20px;"></span>
                    </div>

                    <div style="padding: 30px;">
                        <h6 style="font-weight: 600; margin-bottom: 20px; color: #1a1d29;">
                            <i class="bi bi-info-circle me-2"></i>Account Information
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">USER ID</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewUserId"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">STATUS</small>
                                    <div style="margin-top: 5px;" id="viewUserStatus"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">MUST CHANGE PASSWORD</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewUserMustChange"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">ROLE</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewUserRoleName"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">CREATED AT</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewUserCreated"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="padding: 15px; background: #f9fafb; border-radius: 10px;">
                                    <small style="color: #6b7280; font-weight: 500;">LAST UPDATED</small>
                                    <div style="font-weight: 600; margin-top: 5px;" id="viewUserUpdated"></div>
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
    // LOAD ACTIVE ROLES INTO DROPDOWNS
    // ==========================================
    function loadRoles() {
        $.ajax({
            url: "{{ route('admin.roles.active') }}",
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    let filterOptions = '<option value="">All Roles</option>';
                    let formOptions = '<option value="">-- Select Role --</option>';

                    response.data.forEach(function(role) {
                        filterOptions += `<option value="${role.role_id}">${role.role_name}</option>`;
                        formOptions += `<option value="${role.role_id}">${role.role_name}</option>`;
                    });

                    $('#roleFilter').html(filterOptions);
                    $('#role_id').html(formOptions);
                }
            },
            error: function() {
                console.error('Failed to load roles');
            }
        });
    }

    // Load roles on page load
    loadRoles();

    // ==========================================
    // LOAD USERS
    // ==========================================
    function loadUsers(page = 1) {
        currentPage = page;

        $('#usersTableBody').html(`
            <tr>
                <td colspan="7" class="table-loading">
                    <div class="spinner-custom"></div>
                    <p class="text-muted mt-3">Loading users...</p>
                </td>
            </tr>
        `);

        $.ajax({
            url: "{{ route('admin.users.fetch') }}",
            method: 'GET',
            data: {
                page: page,
                search: $('#searchInput').val(),
                role_id: $('#roleFilter').val(),
                status: $('#statusFilter').val()
            },
            success: function(response) {
                renderUsers(response.data, response.pagination);
                renderPagination(response.pagination);
                $('#totalCount').text('Total: ' + response.pagination.total + ' users');
            },
            error: function() {
                showError('Failed to load users. Please try again.');
            }
        });
    }

    // ==========================================
    // RENDER USERS
    // ==========================================
    function renderUsers(users, pagination) {
        if (users.length === 0) {
            $('#usersTableBody').html(`
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #d1d5db;"></i>
                        <p class="text-muted mt-2">No users found</p>
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        let startNum = pagination.from;

        users.forEach(function(user, index) {
            const statusBadge = user.status == 1
                ? `<span class="badge-active"><i class="bi bi-check-circle-fill me-1"></i>Active</span>`
                : `<span class="badge-inactive"><i class="bi bi-x-circle-fill me-1"></i>Inactive</span>`;

            const youBadge = user.is_self
                ? `<small style="color: #4f46e5; font-size: 0.7rem;">(You)</small>`
                : '';

            let actionButtons = `
                <li><a class="dropdown-item view-btn" href="#" data-id="${user.user_id}">
                    <i class="bi bi-eye me-2"></i>View
                </a></li>
                <li><a class="dropdown-item edit-btn" href="#" data-id="${user.user_id}">
                    <i class="bi bi-pencil me-2"></i>Edit
                </a></li>
            `;

            if (!user.is_self) {
                const toggleText = user.status == 1
                    ? '<i class="bi bi-toggle-off me-2"></i>Deactivate'
                    : '<i class="bi bi-toggle-on me-2"></i>Activate';

                actionButtons += `
                    <li><a class="dropdown-item toggle-btn" href="#" data-id="${user.user_id}">
                        ${toggleText}
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger delete-btn" href="#" data-id="${user.user_id}" data-name="${user.user_name}">
                        <i class="bi bi-trash me-2"></i>Delete
                    </a></li>
                `;
            }

            html += `
                <tr>
                    <td>${startNum + index}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar" style="width:38px; height:38px; font-size:0.85rem;">
                                ${user.initial}
                            </div>
                            <div>
                                <div style="font-weight: 600;">${user.user_name}</div>
                                ${youBadge}
                            </div>
                        </div>
                    </td>
                    <td>${user.email}</td>
                    <td>
                        <span class="badge"
                              style="background: rgba(79, 70, 229, 0.1); color: #4f46e5;
                                     border-radius: 8px; padding: 6px 12px; font-weight: 500;">
                            ${user.role_name}
                        </span>
                    </td>
                    <td>${statusBadge}</td>
                    <td>${user.created_at}</td>
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

        $('#usersTableBody').html(html);
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

        // Previous
        html += `<li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a>
                </li>`;

        // Page numbers
        for (let i = 1; i <= pagination.last_page; i++) {
            html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>`;
        }

        // Next
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
            loadUsers(page);
        }
    });

    // ==========================================
    // SEARCH (with debounce)
    // ==========================================
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadUsers(1);
        }, 500);
    });

    // ==========================================
    // FILTERS
    // ==========================================
    $('#roleFilter, #statusFilter').on('change', function() {
        loadUsers(1);
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#searchInput').val('');
        $('#roleFilter').val('');
        $('#statusFilter').val('all');
        loadUsers(1);
    });

    // Refresh
    $('#refreshBtn').on('click', function() {
        loadUsers(currentPage);
        showToast('success', 'Data refreshed!');
    });

    // ==========================================
    // ADD NEW USER
    // ==========================================
    $('#btnAddUser').on('click', function() {
        resetForm();
        $('#modalTitle').html('<i class="bi bi-person-plus me-2"></i>Add New User');
        $('#submitText').text('Create User');
        $('#form_action').val('create');
        $('#password').attr('required', true);
        $('#password_confirmation').attr('required', true);
        $('#passwordRequired').show();
        $('#passwordConfirmRequired').show();
        $('#passwordHint').addClass('d-none');
        $('#userModal').modal('show');
    });

    // ==========================================
    // EDIT USER
    // ==========================================
    $(document).on('click', '.edit-btn', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');

        $.ajax({
            url: `/admin/users/${userId}/get`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    resetForm();
                    const user = response.data;
                    $('#user_id').val(user.user_id);
                    $('#user_name').val(user.user_name);
                    $('#email').val(user.email);

                    // Ensure roles are loaded before setting value
                    setTimeout(() => {
                        $('#role_id').val(user.role_id);
                    }, 100);

                    $('#status').val(user.status);
                    $('#must_change_password').prop('checked', user.must_change_password == 1);

                    $('#modalTitle').html('<i class="bi bi-pencil-square me-2"></i>Edit User: ' + user.user_name);
                    $('#submitText').text('Update User');
                    $('#form_action').val('update');
                    $('#password').attr('required', false);
                    $('#password_confirmation').attr('required', false);
                    $('#passwordRequired').hide();
                    $('#passwordConfirmRequired').hide();
                    $('#passwordHint').removeClass('d-none');

                    $('#userModal').modal('show');
                }
            },
            error: function() {
                showError('Failed to load user data.');
            }
        });
    });

    // ==========================================
    // VIEW USER
    // ==========================================
    $(document).on('click', '.view-btn', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');

        $.ajax({
            url: `/admin/users/${userId}/get`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const user = response.data;
                    $('#viewUserInitial').text(user.initial);
                    $('#viewUserName').text(user.user_name);
                    $('#viewUserEmail').text(user.email);
                    $('#viewUserRole').text(user.role_name);
                    $('#viewUserId').text('#' + user.user_id);
                    $('#viewUserRoleName').text(user.role_name);
                    $('#viewUserMustChange').text(user.must_change_password == 1 ? 'Yes' : 'No');
                    $('#viewUserCreated').text(user.created_at);
                    $('#viewUserUpdated').text(user.updated_at);

                    const statusHtml = user.status == 1
                        ? `<span class="badge-active">Active</span>`
                        : `<span class="badge-inactive">Inactive</span>`;
                    $('#viewUserStatus').html(statusHtml);

                    $('#viewUserModal').modal('show');
                }
            },
            error: function() {
                showError('Failed to load user data.');
            }
        });
    });

    // ==========================================
    // SUBMIT FORM (Create/Update)
    // ==========================================
    $('#userForm').on('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        $('.error-msg').text('');

        const action = $('#form_action').val();
        const userId = $('#user_id').val();

        let url = "{{ route('admin.users.store') }}";
        let method = 'POST';

        if (action === 'update') {
            url = `/admin/users/${userId}/update`;
            method = 'POST';
        }

        const formData = new FormData(this);
        if (action === 'update') {
            formData.append('_method', 'PUT');
        }

        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#userModal').modal('hide');
                    showToast('success', response.message);
                    loadUsers(currentPage);
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
                    (action === 'create' ? 'Create User' : 'Update User') + '</span>');
            }
        });
    });

    // ==========================================
    // DELETE USER
    // ==========================================
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        const userName = $(this).data('name');

        confirmAction(
            'Delete User?',
            `Are you sure you want to delete "${userName}"? This action cannot be undone.`,
            'Yes, Delete!'
        ).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/users/${userId}/delete`,
                    method: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            loadUsers(currentPage);
                        }
                    },
                    error: function(xhr) {
                        showError(xhr.responseJSON?.message || 'Failed to delete user.');
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
        const userId = $(this).data('id');

        confirmAction(
            'Change Status?',
            'Are you sure you want to change this user\'s status?',
            'Yes, Change!'
        ).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/users/${userId}/toggle-status`,
                    method: 'PATCH',
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            loadUsers(currentPage);
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
        $('#userForm')[0].reset();
        $('#user_id').val('');
        $('.error-msg').text('');
    }

    // ==========================================
    // INITIAL LOAD
    // ==========================================
    loadUsers();

});
</script>
@endpush