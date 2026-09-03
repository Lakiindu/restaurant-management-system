<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Restaurant Manager</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #065f46;
            --sidebar-hover: #047857;
            --primary-color: #10b981;
            --primary-light: #34d399;
        }

        body {
            background-color: #f0f2f5;
            overflow-x: hidden;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand h4 {
            color: #fff;
            margin: 0;
            font-weight: 700;
            font-size: 1.3rem;
        }

        .sidebar-brand span {
            color: var(--primary-light);
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .menu-label {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 10px 25px 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: var(--sidebar-hover);
            color: #fff;
            border-left-color: var(--primary-light);
        }

        .sidebar-menu a i {
            font-size: 1.1rem;
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .top-navbar {
            background: #fff;
            padding: 15px 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a1d29;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--primary-color);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .content-area {
            padding: 30px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }

        /* ===== Shared card/table styles (needed for Roles, Pages, etc.) ===== */
        .custom-table {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .table-header {
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f0;
        }

        .custom-table .table {
            margin-bottom: 0;
        }

        .custom-table .table th {
            background: #f9fafb;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            padding: 12px 20px;
            border: none;
        }

        .custom-table .table td {
            padding: 15px 20px;
            vertical-align: middle;
            border-color: #f0f0f0;
            font-size: 0.9rem;
        }

        .badge-active {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 500;
        }

        .badge-inactive {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 500;
        }

        .table-loading {
            padding: 50px;
            text-align: center;
        }

        .spinner-custom {
            width: 40px;
            height: 40px;
            border: 4px solid #e5e7eb;
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Form controls */
        .form-control,
        .form-select {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1.5px solid #e5e7eb;
            font-size: 0.9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        .input-group-text {
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            border-right: none;
            border-radius: 8px 0 0 8px;
        }

        .input-group .form-control,
        .input-group .form-select {
            border-left: none;
        }

        /* Modal polish */
        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            border-bottom: 1px solid #f0f0f0;
            padding: 20px 25px;
        }

        .modal-body {
            padding: 25px;
        }

        .modal-footer {
            border-top: 1px solid #f0f0f0;
            padding: 15px 25px;
        }

        /* Keep Bootstrap primary closer to manager green on these pages */
        .text-primary {
            color: var(--primary-color) !important;
        }
    </style>
</head>

<body>

    <!-- ================= SIDEBAR ================= -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h4><i class="bi bi-shop"></i> <span>Manager</span> Panel</h4>
        </div>

        <div class="sidebar-menu">
            @php
                $menuCategories = Auth::user()->getNavigationMenu();

                $pageIcons = [
                    'ADMIN_DASHBOARD' => 'bi-grid-1x2-fill',
                    'MANAGER_DASHBOARD' => 'bi-grid-1x2-fill',
                    'USER_LIST' => 'bi-people-fill',
                    'ROLE_LIST' => 'bi-shield-lock-fill',
                    'PERMISSION_MANAGE' => 'bi-key-fill',
                    'CATEGORY_LIST' => 'bi-folder-fill',
                    'PAGE_LIST' => 'bi-file-earmark-text-fill',
                    'OPTION_LIST' => 'bi-lightning-charge-fill',
                ];

                $rolePrefix = strtolower(Auth::user()->role->role_name) . '.';
            @endphp

            @foreach ($menuCategories as $category)
                @php
                    // Build only pages that have a REAL working route
                    $visiblePages = [];

                    foreach ($category->pages as $page) {
                        $routeName = $page->route_name;

                        // Convert admin.xxx -> manager.xxx for non-admin users
                        if (Auth::user()->role_id != 1 && str_starts_with((string) $routeName, 'admin.')) {
                            $routeName = str_replace('admin.', $rolePrefix, $routeName);
                        }

                        // Special case: manager dashboard route
                        if ($page->page_code === 'MANAGER_DASHBOARD') {
                            $routeName = 'manager.dashboard';
                        }

                        if ($routeName && \Illuminate\Support\Facades\Route::has($routeName)) {
                            $visiblePages[] = [
                                'page' => $page,
                                'routeName' => $routeName,
                            ];
                        }
                    }
                @endphp

                {{-- Show category only if it has at least 1 working page --}}
                @if (count($visiblePages) > 0)
                    <div class="menu-label">{{ $category->category_name }}</div>

                    @foreach ($visiblePages as $item)
                        @php
                            $page = $item['page'];
                            $routeName = $item['routeName'];
                            $routeUrl = route($routeName);

                            $isActive = false;
                            $routeParts = explode('.', $routeName);
                            if (count($routeParts) >= 2) {
                                $activePattern = $routeParts[0] . '.' . $routeParts[1] . '.*';
                                $isActive = request()->routeIs($activePattern) || request()->routeIs($routeName);
                            }

                            $icon = $pageIcons[$page->page_code] ?? 'bi-file-earmark';
                        @endphp

                        <a href="{{ $routeUrl }}" class="{{ $isActive ? 'active' : '' }}">
                            <i class="bi {{ $icon }}"></i> {{ $page->page_name }}
                        </a>
                    @endforeach
                @endif
            @endforeach
        </div>
    </div>

    <!-- ================= MAIN CONTENT ================= -->
    <div class="main-content">
        <div class="top-navbar">
            <div>
                <span class="page-title">@yield('page-title', 'Dashboard')</span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn dropdown-toggle d-flex align-items-center gap-2"
                        style="border:none; background:transparent;" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->user_name, 0, 1)) }}
                        </div>
                        <div class="text-start">
                            <div style="font-weight: 600; font-size: 0.85rem;">
                                {{ Auth::user()->user_name }}
                            </div>
                            <div style="font-size: 0.75rem; color: #6b7280;">
                                {{ Auth::user()->role->role_name }}
                            </div>
                        </div>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item text-danger" href="#" id="logoutBtn">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                            <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="content-area">
            @yield('content')
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#logoutBtn').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Logout?',
                text: 'Are you sure you want to logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Yes, Logout'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#logoutForm').submit();
                }
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
