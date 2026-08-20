@extends('layouts.manager')

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
            style="background: #10b981; color: #fff; border-radius: 8px; padding: 10px 20px;">
            <i class="bi bi-plus-lg me-1"></i> Add New User
        </button>
    </div>

    <!-- Filters -->
    <div
        style="background:#fff; border-radius:12px; border:1px solid rgba(0,0,0,0.04); box-shadow:0 1px 3px rgba(0,0,0,0.06); margin-bottom:20px;">
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
                    <button type="button" id="resetFilters" class="btn btn-light w-100" style="border-radius: 8px;">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div
        style="background:#fff; border-radius:12px; border:1px solid rgba(0,0,0,0.04); box-shadow:0 1px 3px rgba(0,0,0,0.06); overflow:hidden;">
        <div style="padding:20px 25px; border-bottom:1px solid #f0f0f0;"
            class="d-flex justify-content-between align-items-center">
            <div>
                <h6 style="font-weight: 600; margin-bottom: 2px;">Users List</h6>
                <small style="color: #6b7280;" id="totalCount">Loading...</small>
            </div>
            <button type="button" id="refreshBtn" class="btn btn-light btn-sm" style="border-radius: 8px;">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>

        <div class="table-responsive">
            <table class="table" style="margin-bottom:0;">
                <thead>
                    <tr>
                        <th
                            style="background:#f9fafb; font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; color:#6b7280; padding:12px 20px; border:none;">
                            #</th>
                        <th
                            style="background:#f9fafb; font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; color:#6b7280; padding:12px 20px; border:none;">
                            User</th>
                        <th
                            style="background:#f9fafb; font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; color:#6b7280; padding:12px 20px; border:none;">
                            Email</th>
                        <th
                            style="background:#f9fafb; font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; color:#6b7280; padding:12px 20px; border:none;">
                            Role</th>
                        <th
                            style="background:#f9fafb; font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; color:#6b7280; padding:12px 20px; border:none;">
                            Status</th>
                        <th
                            style="background:#f9fafb; font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; color:#6b7280; padding:12px 20px; border:none;">
                            Created</th>
                        <th style="background:#f9fafb; font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; color:#6b7280; padding:12px 20px; border:none;"
                            class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr>
                        <td colspan="7" style="padding:50px; text-align:center;">
                            <div class="spinner-border text-success" role="status"></div>
                            <p class="text-muted mt-3">Loading users...</p>
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

    <!-- ADD/EDIT USER MODAL -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:15px; border:none;">
                <div class="modal-header" style="border-bottom:1px solid #f0f0f0; padding:20px 25px;">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="bi bi-person-plus me-2"></i>Add New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="userForm">
                    @csrf
                    <input type="hidden" id="user_id" name="user_id">
                    <input type="hidden" id="form_action" value="create">

                    <div class="modal-body" style="padding:25px;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">
                                    Username <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="user_name" id="user_name" class="form-control"
                                        placeholder="e.g. john_doe" required>
                                </div>
                                <small class="text-danger error-msg" data-field="user_name"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="email" class="form-control"
                                        placeholder="user@restaurant.com" required>
                                </div>
                                <small class="text-danger error-msg" data-field="email"></small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 500;">
                                    Password <span class="text-danger" id="passwordRequired">*</span>
                                    <small class="text-muted d-none" id="passwordHint">(Leave blank to keep
                                        current)</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="password" class="form-control"
                                        placeholder="Min 6 characters">
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

                    <div class="modal-footer" style="border-top:1px solid #f0f0f0; padding:15px 25px;">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius: 8px; padding: 8px 20px;">Cancel</button>
                        <button type="submit" class="btn" id="submitBtn"
                            style="background: #10b981; color: #fff; border-radius: 8px; padding: 8px 20px;">
                            <i class="bi bi-check-lg me-1"></i> <span id="submitText">Create User</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- VIEW USER MODAL -->
    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:15px; border:none;">
                <div class="modal-body p-0">
                    <div
                        style="background: linear-gradient(135deg, #10b981, #059669); padding: 40px; color: #fff; text-align: center; border-radius: 15px 15px 0 0;">
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                            data-bs-dismiss="modal"></button>
                        <div style="width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,0.2);
                                    display:flex;align-items:center;justify-content:center;margin:0 auto 15px;
                                    font-size:2.5rem;font-weight:600;"
                            id="viewUserInitial"></div>
                        <h4 style="font-weight: 700; margin-bottom: 5px;" id="viewUserName"></h4>
                        <p style="opacity: 0.9; margin-bottom: 10px;" id="viewUserEmail"></p>
                        <span class="badge" id="viewUserRole"
                            style="background: rgba(255,255,255,0.2); padding: 6px 15px; border-radius: 20px;"></span>
                    </div>

                    <div style="padding: 30px;">
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

            // Get user permissions
            const userPermissions = {
                canAdd: {{ Auth::user()->hasOptionPermission('USER_ADD') ? 'true' : 'false' }},
                canEdit: {{ Auth::user()->hasOptionPermission('USER_EDIT') ? 'true' : 'false' }},
                canDelete: {{ Auth::user()->hasOptionPermission('USER_DELETE') ? 'true' : 'false' }},
                canView: {{ Auth::user()->hasOptionPermission('USER_VIEW') ? 'true' : 'false' }},
            };

            // Hide Add button if no permission
            if (!userPermissions.canAdd) {
                $('#btnAddUser').hide();
            }

            // Load active roles for dropdown
            function loadRoles() {
                $.ajax({
                    url: "{{ route('manager.roles.active') }}",
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            let filterOpts = '<option value="">All Roles</option>';
                            let formOpts = '<option value="">-- Select Role --</option>';
                            response.data.forEach(function(role) {
                                filterOpts +=
                                    `<option value="${role.role_id}">${role.role_name}</option>`;
                                formOpts +=
                                    `<option value="${role.role_id}">${role.role_name}</option>`;
                            });
                            $('#roleFilter').html(filterOpts);
                            $('#role_id').html(formOpts);
                        }
                    }
                });
            }

            // Load users
            function loadUsers(page = 1) {
                currentPage = page;
                $('#usersTableBody').html(`
            <tr><td colspan="7" style="padding:50px; text-align:center;">
                <div class="spinner-border text-success"></div>
                <p class="text-muted mt-3">Loading users...</p>
            </td></tr>
        `);

                $.ajax({
                    url: "{{ route('manager.users.fetch') }}",
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
                    error: function(xhr) {
                        console.log('Error:', xhr);
                        Swal.fire('Error!', 'Failed to load users. Please try again.', 'error');
                    }
                });
            }

            // Render users
            function renderUsers(users, pagination) {
                if (users.length === 0) {
                    $('#usersTableBody').html(`
                <tr><td colspan="7" style="padding:50px; text-align:center;">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #d1d5db;"></i>
                    <p class="text-muted mt-2">No users found</p>
                </td></tr>
            `);
                    return;
                }

                let html = '';
                let startNum = pagination.from;

                users.forEach(function(user, index) {
                    const statusBadge = user.status == 1 ?
                        `<span style="background:rgba(16,185,129,0.1);color:#10b981;padding:5px 12px;border-radius:20px;font-size:0.78rem;font-weight:500;">
                    <i class="bi bi-check-circle-fill me-1"></i>Active</span>` :
                        `<span style="background:rgba(239,68,68,0.1);color:#ef4444;padding:5px 12px;border-radius:20px;font-size:0.78rem;font-weight:500;">
                    <i class="bi bi-x-circle-fill me-1"></i>Inactive</span>`;

                    const youBadge = user.is_self ?
                        `<small style="color: #10b981; font-size: 0.7rem;">(You)</small>` : '';

                    // Build action buttons based on permissions
                    let actionButtons = '';

                    if (userPermissions.canView) {
                        actionButtons += `<li><a class="dropdown-item view-btn" href="#" data-id="${user.user_id}">
                    <i class="bi bi-eye me-2"></i>View</a></li>`;
                    }

                    if (userPermissions.canEdit) {
                        actionButtons += `<li><a class="dropdown-item edit-btn" href="#" data-id="${user.user_id}">
                    <i class="bi bi-pencil me-2"></i>Edit</a></li>`;
                    }

                    if (!user.is_self) {
                        actionButtons += `<li><a class="dropdown-item toggle-btn" href="#" data-id="${user.user_id}">
                    ${user.status == 1
                        ? '<i class="bi bi-toggle-off me-2"></i>Deactivate'
                        : '<i class="bi bi-toggle-on me-2"></i>Activate'}
                </a></li>`;

                        if (userPermissions.canDelete) {
                            actionButtons += `
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger delete-btn" href="#"
                               data-id="${user.user_id}" data-name="${user.user_name}">
                            <i class="bi bi-trash me-2"></i>Delete</a></li>`;
                        }
                    }

                    html += `
                <tr>
                    <td style="padding:15px 20px; vertical-align:middle; border-color:#f0f0f0; font-size:0.9rem;">${startNum + index}</td>
                    <td style="padding:15px 20px; vertical-align:middle; border-color:#f0f0f0;">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:38px;height:38px;border-radius:50%;background:#10b981;color:#fff;
                                        display:flex;align-items:center;justify-content:center;font-weight:600;font-size:0.85rem;">
                                ${user.initial}
                            </div>
                            <div>
                                <div style="font-weight: 600;">${user.user_name}</div>
                                ${youBadge}
                            </div>
                        </div>
                    </td>
                    <td style="padding:15px 20px; vertical-align:middle; border-color:#f0f0f0; font-size:0.9rem;">${user.email}</td>
                    <td style="padding:15px 20px; vertical-align:middle; border-color:#f0f0f0;">
                        <span class="badge" style="background:rgba(16,185,129,0.1);color:#10b981;border-radius:8px;padding:6px 12px;font-weight:500;">
                            ${user.role_name}
                        </span>
                    </td>
                    <td style="padding:15px 20px; vertical-align:middle; border-color:#f0f0f0;">${statusBadge}</td>
                    <td style="padding:15px 20px; vertical-align:middle; border-color:#f0f0f0; font-size:0.9rem;">${user.created_at}</td>
                    <td style="padding:15px 20px; vertical-align:middle; border-color:#f0f0f0;" class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm" style="background: #f3f4f6; border-radius: 6px;"
                                    data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end"
                                style="border-radius: 10px; border: 1px solid #e5e7eb; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                                ${actionButtons}
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
                });

                $('#usersTableBody').html(html);
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
                        <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a></li>`;
                for (let i = 1; i <= pagination.last_page; i++) {
                    html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                }
                html += `<li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a></li>`;
                $('#paginationLinks').html(html);
            }

            // Pagination click
            $(document).on('click', '#paginationLinks .page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active'))
                    loadUsers(page);
            });

            // Search
            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => loadUsers(1), 500);
            });

            // Filters
            $('#roleFilter, #statusFilter').on('change', () => loadUsers(1));

            $('#resetFilters').on('click', function() {
                $('#searchInput').val('');
                $('#roleFilter').val('');
                $('#statusFilter').val('all');
                loadUsers(1);
            });

            $('#refreshBtn').on('click', function() {
                loadUsers(currentPage);
                Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    })
                    .fire({
                        icon: 'success',
                        title: 'Refreshed!'
                    });
            });

            // Add User
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

            // Edit User
            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                const userId = $(this).data('id');
                $.ajax({
                    url: `/manager/users/${userId}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            resetForm();
                            const user = response.data;
                            $('#user_id').val(user.user_id);
                            $('#user_name').val(user.user_name);
                            $('#email').val(user.email);
                            setTimeout(() => $('#role_id').val(user.role_id), 100);
                            $('#status').val(user.status);
                            $('#must_change_password').prop('checked', user
                                .must_change_password == 1);
                            $('#modalTitle').html(
                                '<i class="bi bi-pencil-square me-2"></i>Edit: ' + user
                                .user_name);
                            $('#submitText').text('Update User');
                            $('#form_action').val('update');
                            $('#password').attr('required', false);
                            $('#password_confirmation').attr('required', false);
                            $('#passwordRequired').hide();
                            $('#passwordConfirmRequired').hide();
                            $('#passwordHint').removeClass('d-none');
                            $('#userModal').modal('show');
                        }
                    }
                });
            });

            // View User
            $(document).on('click', '.view-btn', function(e) {
                e.preventDefault();
                const userId = $(this).data('id');
                $.ajax({
                    url: `/manager/users/${userId}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const user = response.data;
                            $('#viewUserInitial').text(user.initial);
                            $('#viewUserName').text(user.user_name);
                            $('#viewUserEmail').text(user.email);
                            $('#viewUserRole').text(user.role_name);
                            $('#viewUserId').text('#' + user.user_id);
                            $('#viewUserCreated').text(user.created_at);
                            $('#viewUserUpdated').text(user.updated_at);
                            $('#viewUserStatus').html(user.status == 1 ?
                                '<span style="background:rgba(16,185,129,0.1);color:#10b981;padding:5px 12px;border-radius:20px;font-size:0.78rem;">Active</span>' :
                                '<span style="background:rgba(239,68,68,0.1);color:#ef4444;padding:5px 12px;border-radius:20px;font-size:0.78rem;">Inactive</span>'
                                );
                            $('#viewUserModal').modal('show');
                        }
                    }
                });
            });

            // Submit Form
            $('#userForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-msg').text('');

                const action = $('#form_action').val();
                const userId = $('#user_id').val();
                let url = "{{ route('manager.users.store') }}";
                const formData = new FormData(this);

                if (action === 'update') {
                    url = `/manager/users/${userId}/update`;
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
                            $('#userModal').modal('hide');
                            Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                })
                                .fire({
                                    icon: 'success',
                                    title: response.message
                                });
                            loadUsers(currentPage);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(f => $(`.error-msg[data-field="${f}"]`)
                                .text(errors[f][0]));
                        } else {
                            Swal.fire('Error!', xhr.responseJSON?.message ||
                                'Something went wrong!', 'error');
                        }
                    },
                    complete: function() {
                        $('#submitBtn').prop('disabled', false).html(
                            '<i class="bi bi-check-lg me-1"></i> <span id="submitText">' +
                            (action === 'create' ? 'Create User' : 'Update User') +
                            '</span>');
                    }
                });
            });

            // Delete User
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                const userId = $(this).data('id');
                const userName = $(this).data('name');

                Swal.fire({
                    title: 'Delete User?',
                    text: `Delete "${userName}"? This cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, Delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/manager/users/${userId}/delete`,
                            method: 'DELETE',
                            success: function(response) {
                                if (response.success) {
                                    Swal.mixin({
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000
                                        })
                                        .fire({
                                            icon: 'success',
                                            title: response.message
                                        });
                                    loadUsers(currentPage);
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON?.message ||
                                    'Failed!', 'error');
                            }
                        });
                    }
                });
            });

            // Toggle Status
            $(document).on('click', '.toggle-btn', function(e) {
                e.preventDefault();
                const userId = $(this).data('id');
                Swal.fire({
                    title: 'Change Status?',
                    text: 'Change this user\'s status?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'Yes!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/manager/users/${userId}/toggle-status`,
                            method: 'PATCH',
                            success: function(response) {
                                if (response.success) {
                                    Swal.mixin({
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000
                                        })
                                        .fire({
                                            icon: 'success',
                                            title: response.message
                                        });
                                    loadUsers(currentPage);
                                }
                            }
                        });
                    }
                });
            });

            function resetForm() {
                $('#userForm')[0].reset();
                $('#user_id').val('');
                $('.error-msg').text('');
            }

            // Initial load
            loadRoles();
            loadUsers();
        });
    </script>
@endpush
