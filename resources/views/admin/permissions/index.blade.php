@extends('layouts.admin')

@section('title', 'Permissions')
@section('page-title', 'Permission Management')

@push('styles')
    <style>
        .role-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s ease;
            margin-bottom: 15px;
        }

        .role-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .role-card.selected {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, #eef2ff, #f0f0ff);
        }

        .role-card.admin-role {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .role-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .category-block {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .category-header {
            background: linear-gradient(135deg, #f9fafb, #f3f4f6);
            padding: 15px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .category-title {
            font-weight: 600;
            color: #1a1d29;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-row {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s ease;
        }

        .page-row:last-child {
            border-bottom: none;
        }

        .page-row:hover {
            background: #fafbfc;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .options-wrapper {
            margin-top: 12px;
            padding: 12px 15px;
            background: #f9fafb;
            border-radius: 8px;
            display: none;
        }

        .options-wrapper.show {
            display: block;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 8px;
        }

        .option-item {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background: #fff;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.85rem;
        }

        .option-item:hover {
            border-color: var(--primary-color);
            background: #eef2ff;
        }

        .option-item.checked {
            background: #eef2ff;
            border-color: var(--primary-color);
            color: var(--primary-color);
            font-weight: 500;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-input {
            cursor: pointer;
            width: 20px;
            height: 20px;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 15px;
        }

        .permission-summary {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #fff;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .summary-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .action-btn {
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 500;
            border: none;
        }

        .btn-select-all {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .btn-select-all:hover {
            background: #10b981;
            color: #fff;
        }

        .btn-clear-all {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .btn-clear-all:hover {
            background: #ef4444;
            color: #fff;
        }
    </style>
@endpush

@section('content')

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-weight: 700; margin-bottom: 5px; color: #1a1d29;">Permission Management</h4>
            <p style="color: #6b7280; margin-bottom: 0;">Assign pages and actions to roles</p>
        </div>
    </div>

    <div class="row">
        <!-- LEFT: Roles List -->
        <div class="col-lg-4">
            <div class="custom-table">
                <div class="table-header">
                    <h6 style="font-weight: 600; margin-bottom: 2px;">
                        <i class="bi bi-shield-lock me-2"></i>Select Role
                    </h6>
                    <small style="color: #6b7280;">Choose a role to manage permissions</small>
                </div>
                <div style="padding: 20px; max-height: 600px; overflow-y: auto;" id="rolesList">
                    <div class="table-loading">
                        <div class="spinner-custom"></div>
                        <p class="text-muted mt-3">Loading roles...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Permissions -->
        <div class="col-lg-8">
            <div id="permissionsContainer">
                <div class="custom-table">
                    <div class="empty-state">
                        <i class="bi bi-shield-lock"></i>
                        <h5 style="color: #6b7280; font-weight: 500;">No Role Selected</h5>
                        <p style="color: #9ca3af; font-size: 0.9rem;">
                            Please select a role from the left panel to manage its permissions
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            let currentRoleId = null;
            let allPermissionsData = null;

            // ==========================================
            // LOAD ROLES
            // ==========================================
            function loadRoles() {
                $.ajax({
                    url: "{{ route('admin.permissions.roles') }}",
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            renderRoles(response.data);
                        }
                    },
                    error: function() {
                        showError('Failed to load roles.');
                    }
                });
            }

            // ==========================================
            // RENDER ROLES
            // ==========================================
            function renderRoles(roles) {
                if (roles.length === 0) {
                    $('#rolesList').html(`
                <div class="empty-state">
                    <i class="bi bi-shield"></i>
                    <p class="text-muted">No roles found. Create roles first.</p>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-sm"
                       style="background: var(--primary-color); color: #fff; border-radius: 8px;">
                        Go to Roles
                    </a>
                </div>
            `);
                    return;
                }

                let html = '';
                roles.forEach(function(role) {
                    const isAdmin = role.role_id == 1;
                    const adminClass = isAdmin ? 'admin-role' : '';
                    const adminBadge = isAdmin ?
                        `<span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;
                        border-radius: 6px; padding: 3px 8px; font-size: 0.7rem;">
                        <i class="bi bi-lock-fill"></i> Protected
                   </span>` :
                        '';

                    html += `
                <div class="role-card ${adminClass}" data-id="${role.role_id}" data-name="${role.role_name}">
                    <div class="d-flex align-items-center gap-3">
                        <div class="role-icon">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div style="font-weight: 600; color: #1a1d29;">
                                ${role.role_name} ${adminBadge}
                            </div>
                            <small style="color: #6b7280;">
                                ${role.description || 'No description'}
                            </small>
                        </div>
                        <i class="bi bi-chevron-right" style="color: #9ca3af;"></i>
                    </div>
                </div>
            `;
                });

                $('#rolesList').html(html);
            }

            // ==========================================
            // ROLE CARD CLICK
            // ==========================================
            $(document).on('click', '.role-card', function() {
                const roleId = $(this).data('id');
                const roleName = $(this).data('name');

                if ($(this).hasClass('admin-role')) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Admin Role',
                        text: 'Admin role has full access to everything and cannot be modified.',
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                // Update UI
                $('.role-card').removeClass('selected');
                $(this).addClass('selected');

                currentRoleId = roleId;
                loadPermissions(roleId, roleName);
            });

            // ==========================================
            // LOAD PERMISSIONS FOR ROLE
            // ==========================================
            function loadPermissions(roleId, roleName) {
                $('#permissionsContainer').html(`
            <div class="custom-table">
                <div class="table-loading">
                    <div class="spinner-custom"></div>
                    <p class="text-muted mt-3">Loading permissions for ${roleName}...</p>
                </div>
            </div>
        `);

                $.ajax({
                    url: `/admin/permissions/role/${roleId}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            allPermissionsData = response.data;
                            renderPermissions(response.role, response.data);
                        }
                    },
                    error: function() {
                        showError('Failed to load permissions.');
                    }
                });
            }

            // ==========================================
            // RENDER PERMISSIONS
            // ==========================================
            function renderPermissions(role, categories) {
                // Count totals
                let totalPages = 0;
                let checkedPages = 0;
                let totalOptions = 0;
                let checkedOptions = 0;

                categories.forEach(cat => {
                    cat.pages.forEach(page => {
                        totalPages++;
                        if (page.has_permission) checkedPages++;
                        page.options.forEach(opt => {
                            totalOptions++;
                            if (opt.has_permission) checkedOptions++;
                        });
                    });
                });

                let html = `
            <!-- Summary -->
            <div class="permission-summary">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 style="font-weight: 700; margin-bottom: 5px;">
                            <i class="bi bi-shield-lock-fill me-2"></i>${role.role_name}
                        </h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="summary-badge">
                                <i class="bi bi-file-earmark me-1"></i>
                                <span id="pageCountBadge">${checkedPages}</span> / ${totalPages} Pages
                            </span>
                            <span class="summary-badge">
                                <i class="bi bi-lightning me-1"></i>
                                <span id="optionCountBadge">${checkedOptions}</span> / ${totalOptions} Actions
                            </span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="action-btn btn-select-all" id="btnSelectAll">
                            <i class="bi bi-check-all"></i> Select All
                        </button>
                        <button class="action-btn btn-clear-all" id="btnClearAll">
                            <i class="bi bi-x-lg"></i> Clear All
                        </button>
                    </div>
                </div>
            </div>
        `;

                if (categories.length === 0) {
                    html += `
                <div class="custom-table">
                    <div class="empty-state">
                        <i class="bi bi-folder-x"></i>
                        <p>No pages/categories available in the system.</p>
                    </div>
                </div>
            `;
                    $('#permissionsContainer').html(html);
                    return;
                }

                // Render categories
                categories.forEach(function(category) {
                    if (category.pages.length === 0) return;

                    html += `
                <div class="category-block">
                    <div class="category-header">
                        <h6 class="category-title">
                            <i class="bi bi-folder-fill" style="color: #4f46e5;"></i>
                            ${category.category_name}
                            <small style="font-weight: normal; color: #6b7280;">
                                (${category.pages.length} page${category.pages.length > 1 ? 's' : ''})
                            </small>
                        </h6>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input category-toggle"
                                   type="checkbox"
                                   data-category="${category.category_id}"
                                   title="Toggle all pages in this category">
                        </div>
                    </div>
            `;

                    category.pages.forEach(function(page) {
                        const checkedClass = page.has_permission ? 'checked' : '';
                        const optionsShow = page.has_permission ? 'show' : '';

                        html += `
                    <div class="page-row" data-page-code="${page.page_code}">
                        <div class="page-header">
                            <div class="d-flex align-items-center gap-3 flex-grow-1">
                                <div class="form-check m-0">
                                    <input class="form-check-input page-checkbox"
                                           type="checkbox"
                                           value="${page.page_code}"
                                           data-category="${category.category_id}"
                                           ${page.has_permission ? 'checked' : ''}>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #1a1d29;">
                                        <i class="bi bi-file-earmark-text me-1 text-primary"></i>
                                        ${page.page_name}
                                    </div>
                                    <small style="color: #6b7280;">
                                        ${page.description || page.page_code}
                                    </small>
                                </div>
                            </div>
                            ${page.options.length > 0 ? `
                                        <span class="badge" style="background: #f3f4f6; color: #6b7280;
                                              border-radius: 6px; padding: 4px 10px; font-size: 0.75rem;">
                                            <i class="bi bi-lightning-fill"></i> ${page.options.length} action${page.options.length > 1 ? 's' : ''}
                                        </span>
                                    ` : ''}
                        </div>
                `;

                        // Render options if exist
                        if (page.options.length > 0) {
                            html += `<div class="options-wrapper ${optionsShow}" data-page="${page.page_code}">
                                <small style="color: #6b7280; font-weight: 500; display: block; margin-bottom: 8px;">
                                    <i class="bi bi-lightning-charge-fill me-1"></i>Available Actions:
                                </small>
                                <div class="options-grid">`;

                            page.options.forEach(function(opt) {
                                const optChecked = opt.has_permission ? 'checked' : '';
                                const optCheckedClass = opt.has_permission ? 'checked' : '';

                                html += `
                            <label class="option-item ${optCheckedClass}" data-option="${opt.option_code}">
                                <input class="form-check-input option-checkbox me-2"
                                       type="checkbox"
                                       value="${opt.option_code}"
                                       data-page-code="${page.page_code}"
                                       style="width: 16px; height: 16px;"
                                       ${opt.has_permission ? 'checked' : ''}>
                                ${opt.option_name}
                            </label>
                        `;
                            });

                            html += `   </div>
                             </div>`;
                        }

                        html += `</div>`;
                    });

                    html += `</div>`;
                });

                // Save button
                html += `
            <div class="d-flex justify-content-end gap-2 mt-4">
                <button class="btn btn-light" id="btnResetChanges"
                        style="border-radius: 8px; padding: 10px 25px;">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
                <button class="btn btn-danger" id="btnClearPermissions"
                        style="border-radius: 8px; padding: 10px 25px;">
                    <i class="bi bi-trash me-1"></i> Clear All
                </button>
                <button class="btn" id="btnSavePermissions"
                        style="background: var(--primary-color); color: #fff;
                               border-radius: 8px; padding: 10px 30px; font-weight: 600;">
                    <i class="bi bi-check-lg me-1"></i> Save Permissions
                </button>
            </div>
        `;

                $('#permissionsContainer').html(html);

                // Update category toggles
                updateCategoryToggles();
            }

            // ==========================================
            // PAGE CHECKBOX CHANGE
            // ==========================================
            $(document).on('change', '.page-checkbox', function() {
                const pageCode = $(this).val();
                const isChecked = $(this).is(':checked');
                const optionsWrapper = $(`.options-wrapper[data-page="${pageCode}"]`);

                if (isChecked) {
                    optionsWrapper.addClass('show');
                } else {
                    optionsWrapper.removeClass('show');
                    // Uncheck all options for this page
                    $(`.option-checkbox[data-page-code="${pageCode}"]`).prop('checked', false);
                    $(`.option-item`).each(function() {
                        if ($(this).find('.option-checkbox').data('page-code') === pageCode) {
                            $(this).removeClass('checked');
                        }
                    });
                }

                updateCounts();
                updateCategoryToggles();
            });

            // ==========================================
            // OPTION CHECKBOX CHANGE
            // ==========================================
            $(document).on('change', '.option-checkbox', function() {
                const optionCode = $(this).val();
                const pageCode = $(this).data('page-code');
                const isChecked = $(this).is(':checked');

                // Update visual
                $(this).closest('.option-item').toggleClass('checked', isChecked);

                // If any option is checked, auto-check the page
                if (isChecked) {
                    $(`.page-checkbox[value="${pageCode}"]`).prop('checked', true);
                    $(`.options-wrapper[data-page="${pageCode}"]`).addClass('show');
                }

                updateCounts();
                updateCategoryToggles();
            });

            // ==========================================
            // CATEGORY TOGGLE
            // ==========================================
            $(document).on('change', '.category-toggle', function() {
                const categoryId = $(this).data('category');
                const isChecked = $(this).is(':checked');

                // Toggle all pages in this category
                $(`.page-checkbox[data-category="${categoryId}"]`).each(function() {
                    $(this).prop('checked', isChecked).trigger('change');
                });
            });

            // ==========================================
            // UPDATE CATEGORY TOGGLES
            // ==========================================
            function updateCategoryToggles() {
                $('.category-toggle').each(function() {
                    const categoryId = $(this).data('category');
                    const pages = $(`.page-checkbox[data-category="${categoryId}"]`);
                    const checkedPages = pages.filter(':checked');

                    if (pages.length === checkedPages.length && pages.length > 0) {
                        $(this).prop('checked', true).prop('indeterminate', false);
                    } else if (checkedPages.length === 0) {
                        $(this).prop('checked', false).prop('indeterminate', false);
                    } else {
                        $(this).prop('checked', false).prop('indeterminate', true);
                    }
                });
            }

            // ==========================================
            // UPDATE COUNTS
            // ==========================================
            function updateCounts() {
                const checkedPages = $('.page-checkbox:checked').length;
                const checkedOptions = $('.option-checkbox:checked').length;

                $('#pageCountBadge').text(checkedPages);
                $('#optionCountBadge').text(checkedOptions);
            }

            // ==========================================
            // SELECT ALL
            // ==========================================
            $(document).on('click', '#btnSelectAll', function() {
                $('.page-checkbox').prop('checked', true);
                $('.option-checkbox').prop('checked', true);
                $('.options-wrapper').addClass('show');
                $('.option-item').addClass('checked');
                updateCounts();
                updateCategoryToggles();
                showToast('success', 'All permissions selected');
            });

            // ==========================================
            // CLEAR ALL (unchecks visually)
            // ==========================================
            $(document).on('click', '#btnClearAll', function() {
                $('.page-checkbox').prop('checked', false);
                $('.option-checkbox').prop('checked', false);
                $('.options-wrapper').removeClass('show');
                $('.option-item').removeClass('checked');
                updateCounts();
                updateCategoryToggles();
                showToast('info', 'All permissions unchecked (not saved yet)');
            });

            // ==========================================
            // RESET CHANGES
            // ==========================================
            $(document).on('click', '#btnResetChanges', function() {
                if (currentRoleId) {
                    loadPermissions(currentRoleId, $('.role-card.selected').data('name'));
                    showToast('info', 'Changes reset');
                }
            });

            // ==========================================
            // CLEAR ALL PERMISSIONS FROM DB
            // ==========================================
            $(document).on('click', '#btnClearPermissions', function() {
                confirmAction(
                    'Clear All Permissions?',
                    'This will remove ALL permissions for this role from the database. Are you sure?',
                    'Yes, Clear All!'
                ).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/admin/permissions/clear/${currentRoleId}`,
                            method: 'DELETE',
                            success: function(response) {
                                if (response.success) {
                                    showToast('success', response.message);
                                    loadPermissions(currentRoleId, $(
                                        '.role-card.selected').data('name'));
                                }
                            },
                            error: function(xhr) {
                                showError(xhr.responseJSON?.message ||
                                    'Failed to clear permissions.');
                            }
                        });
                    }
                });
            });

            // ==========================================
            // SAVE PERMISSIONS
            // ==========================================
            $(document).on('click', '#btnSavePermissions', function() {
                if (!currentRoleId) {
                    showError('Please select a role first.');
                    return;
                }

                // Collect checked pages and options
                const pageCodes = [];
                const optionCodes = [];

                $('.page-checkbox:checked').each(function() {
                    pageCodes.push($(this).val());
                });

                $('.option-checkbox:checked').each(function() {
                    optionCodes.push($(this).val());
                });

                const btn = $(this);
                const originalHtml = btn.html();
                btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm"></span> Saving...');

                $.ajax({
                    url: "{{ route('admin.permissions.save') }}",
                    method: 'POST',
                    data: {
                        role_id: currentRoleId,
                        page_codes: pageCodes,
                        option_codes: optionCodes,
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Saved!',
                                html: `${response.message}<br><br>
                               <small>
                                    <strong>${response.summary.pages_count}</strong> pages<br>
                                    <strong>${response.summary.options_count}</strong> actions
                               </small>`,
                                confirmButtonColor: '#4f46e5',
                                timer: 2500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        showError(xhr.responseJSON?.message || 'Failed to save permissions.');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Initial load
            loadRoles();
        });
    </script>
@endpush
