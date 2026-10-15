@php
    $panel = $panel ?? (auth()->user()->role->role_name === 'Manager' ? 'manager' : 'admin');
@endphp
@extends($panel === 'manager' ? 'layouts.manager' : 'layouts.admin')

@section('title', 'GRN History')
@section('page-title', 'GRN History')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 style="font-weight: 700; margin-bottom: 5px; color: #1a1d29;">Goods Received Notes (GRN)</h4>
            <p style="color: #6b7280; margin-bottom: 0;">History of all received inventory stock and invoices</p>
        </div>
        <a href="{{ route($panel . '.grn.create') }}" class="btn"
            style="background: var(--primary-color); color: #fff; border-radius: 8px; padding: 10px 20px;">
            <i class="bi bi-plus-lg me-1"></i> Create New GRN
        </a>
    </div>

    <!-- Filters -->
    <div class="custom-table mb-4">
        <div style="padding: 20px 25px;">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" id="searchInput" class="form-control"
                        placeholder="🔍 Search by GRN No, Supplier, or Ref No...">
                </div>
                <div class="col-md-2">
                    <select id="statusFilter" class="form-select">
                        <option value="all">All Status</option>
                        <option value="Completed">Completed</option>
                        <option value="Pending">Pending</option>
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
                <h6 style="font-weight: 600; margin-bottom: 2px;">GRN Records</h6>
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
                        <th>GRN No.</th>
                        <th>Supplier</th>
                        <th>Store / Warehouse</th>
                        <th>GRN Date</th>
                        <th>Grand Total</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="grnTableBody">
                    <tr>
                        <td colspan="8" class="table-loading">
                            <div class="spinner-custom"></div>
                            <p class="text-muted mt-3">Loading GRN history...</p>
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

    <!-- VIEW GRN DETAILS MODAL -->
    <div class="modal fade" id="viewGrnModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
                <div style="background: linear-gradient(135deg, #4f46e5, #6366f1); padding: 30px; color: #fff;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-white text-primary mb-2" id="modalGrnStatus"></span>
                            <h3 style="font-weight: 800; margin: 0;" id="modalGrnNo"></h3>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                </div>

                <div class="modal-body p-4 bg-light">
                    <!-- Header Info -->
                    <div class="p-3 bg-white mb-3" style="border-radius: 10px; border: 1px solid #e5e7eb;">
                        <div class="row g-3" style="font-size: 0.9rem;">
                            <div class="col-md-3">
                                <small class="text-muted d-block fw-medium">SUPPLIER</small>
                                <span class="fw-bold" id="modalSupplier"></span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block fw-medium">STORE / WAREHOUSE</small>
                                <span class="fw-bold" id="modalStore"></span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block fw-medium">GRN DATE</small>
                                <span class="fw-bold" id="modalGrnDate"></span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block fw-medium">REF / INVOICE NO.</small>
                                <span class="fw-bold" id="modalRefNo"></span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block fw-medium">NOTE</small>
                                <span id="modalNote"></span>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <small class="text-muted d-block fw-medium">CREATED BY</small>
                                <span id="modalCreatedBy"></span> (<span id="modalCreatedAt"></span>)
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div style="background: #fff; border-radius: 10px; border: 1px solid #e5e7eb; overflow: hidden;">
                        <table class="table mb-0">
                            <thead style="background: #f9fafb;">
                                <tr>
                                    <th style="font-size: 0.75rem; color: #6b7280; font-weight: 700;">ITEM DETAILS</th>
                                    <th style="font-size: 0.75rem; color: #6b7280; font-weight: 700;">BATCH / EXP</th>
                                    <th style="font-size: 0.75rem; color: #6b7280; font-weight: 700;" class="text-center">
                                        QTY</th>
                                    <th style="font-size: 0.75rem; color: #6b7280; font-weight: 700;" class="text-end">
                                        UNIT COST</th>
                                    <th style="font-size: 0.75rem; color: #6b7280; font-weight: 700;" class="text-end">
                                        AMOUNT</th>
                                </tr>
                            </thead>
                            <tbody id="modalItemsBody">
                                <!-- JS Items -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary -->
                    <div class="row justify-content-end mt-3">
                        <div class="col-md-5">
                            <div class="p-3 bg-white"
                                style="border-radius: 10px; border: 1px solid #e5e7eb; font-size: 0.9rem;">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Sub Total:</span>
                                    <span class="fw-bold" id="modalSubTotal"></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Discount:</span>
                                    <span class="fw-bold text-danger" id="modalDiscount"></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">VAT / Tax:</span>
                                    <span class="fw-bold" id="modalVat"></span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between fs-6 fw-bold" style="color: #4f46e5;">
                                    <span>Grand Total:</span>
                                    <span id="modalGrandTotal"></span>
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
                canView: @json(auth()->user()->hasOptionPermission('GRN_VIEW')),
            };

            let currentPage = 1;
            let searchTimeout;

            function loadGrns(page = 1) {
                currentPage = page;
                $('#grnTableBody').html(
                    `<tr><td colspan="8" class="table-loading"><div class="spinner-custom"></div><p class="text-muted mt-3">Loading GRN history...</p></td></tr>`
                );

                $.ajax({
                    url: `/${panel}/grn/fetch`,
                    method: 'GET',
                    data: {
                        page: page,
                        search: $('#searchInput').val(),
                        status: $('#statusFilter').val(),
                    },
                    success: function(response) {
                        renderGrns(response.data, response.pagination);
                        renderPagination(response.pagination);
                        $('#totalCount').text('Total: ' + response.pagination.total + ' records');
                    },
                    error: function() {
                        if (typeof showError === 'function') showError('Failed to load GRN history.');
                        else Swal.fire('Error', 'Failed to load GRN history.', 'error');
                    }
                });
            }

            function renderGrns(grns, pagination) {
                if (grns.length === 0) {
                    $('#grnTableBody').html(
                        `<tr><td colspan="8" class="text-center py-5"><i class="bi bi-file-earmark-x" style="font-size: 3rem; color: #d1d5db;"></i><p class="text-muted mt-2">No GRNs found</p></td></tr>`
                    );
                    return;
                }

                let html = '';
                let startNum = pagination.from;

                grns.forEach(function(grn, index) {
                    const statusBadge =
                        `<span class="badge-active"><i class="bi bi-check-circle-fill me-1"></i>${grn.status}</span>`;

                    let actionButtons = '';
                    if (userPermissions.canView) {
                        actionButtons +=
                            `<li><a class="dropdown-item view-btn" href="#" data-id="${grn.id}"><i class="bi bi-eye me-2"></i>View Details</a></li>`;
                    } else {
                        actionButtons = `<li><span class="dropdown-item text-muted">No actions</span></li>`;
                    }

                    html += `
                <tr>
                    <td>${startNum + index}</td>
                    <td><strong class="text-primary">${grn.grn_no}</strong></td>
                    <td><strong>${grn.supplier_name}</strong></td>
                    <td><span class="badge bg-light text-dark border">${grn.store_name}</span></td>
                    <td>${grn.grn_date}</td>
                    <td class="fw-bold text-success">Rs ${grn.grand_total}</td>
                    <td>${statusBadge}</td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm" style="background:#f3f4f6; border-radius:6px;" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end" style="border-radius:10px; border:1px solid #e5e7eb;">${actionButtons}</ul>
                        </div>
                    </td>
                </tr>
            `;
                });
                $('#grnTableBody').html(html);
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
                    loadGrns(page);
            });

            $('#searchInput').on('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => loadGrns(1), 500);
            });
            $('#statusFilter').on('change', () => loadGrns(1));
            $('#resetFilters').on('click', function() {
                $('#searchInput').val('');
                $('#statusFilter').val('all');
                loadGrns(1);
            });
            $('#refreshBtn').on('click', function() {
                loadGrns(currentPage);
                if (typeof showToast === 'function') showToast('success', 'Refreshed!');
            });

            // View GRN Modal
            $(document).on('click', '.view-btn', function(e) {
                e.preventDefault();
                const grnId = $(this).data('id');

                $.ajax({
                    url: `/${panel}/grn/${grnId}/get`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const grn = response.data;
                            $('#modalGrnNo').text(grn.grn_no);
                            $('#modalGrnStatus').text(grn.status);
                            $('#modalSupplier').text(grn.supplier_name);
                            $('#modalStore').text(grn.store_name);
                            $('#modalGrnDate').text(grn.grn_date);
                            $('#modalRefNo').text(grn.ref_no);
                            $('#modalNote').text(grn.note);
                            $('#modalCreatedBy').text(grn.created_by);
                            $('#modalCreatedAt').text(grn.created_at);

                            $('#modalSubTotal').text('Rs ' + grn.sub_total);
                            $('#modalDiscount').text('-Rs ' + grn.discount_amount);
                            $('#modalVat').text('+Rs ' + grn.vat_amount);
                            $('#modalGrandTotal').text('Rs ' + grn.grand_total);;

                            let itemsHtml = '';
                            grn.items.forEach(function(item) {
                                itemsHtml += `
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary">${item.item_name}</div>
                                    <small class="text-muted">Code: ${item.item_code}</small>
                                </td>
                                <td>${item.batch_exp}</td>
                                <td class="text-center"><strong>${item.qty}</strong> ${item.unit}</td>
                                <td class="text-end">Rs ${item.cost}</td>
                                <td class="text-end fw-bold text-success">Rs ${item.amount}</td>
                            </tr>
                        `;
                            });
                            $('#modalItemsBody').html(itemsHtml);
                            $('#viewGrnModal').modal('show');
                        }
                    }
                });
            });

            loadGrns();
        });
    </script>
@endpush
