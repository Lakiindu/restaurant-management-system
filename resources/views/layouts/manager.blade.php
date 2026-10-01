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

        /* Collapsible Menu Styles */
        .menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 25px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
        }

        .menu-header:hover,
        .menu-header[aria-expanded="true"] {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .menu-header .arrow-icon {
            transition: transform 0.3s ease;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .menu-header[aria-expanded="true"] .arrow-icon {
            transform: rotate(180deg);
        }

        .submenu {
            background: rgba(0, 0, 0, 0.2);
            padding: 5px 0;
            display: flex;
            /* ADDED THIS */
            flex-direction: column;
            /* ADDED THIS to force vertical stacking */
        }

        .submenu a {
            display: flex;
            /* ADDED THIS */
            align-items: center;
            /* ADDED THIS */
            width: 100%;
            /* ADDED THIS */
            padding: 10px 25px 10px 50px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            border-left: none !important;
            text-decoration: none;
            /* Prevent underlines */
        }

        .submenu a:hover,
        .submenu a.active {
            color: #fff;
            background: transparent !important;
        }

        .submenu a .bullet {
            font-size: 1rem;
            margin-right: 10px;
            opacity: 0.5;
            transition: all 0.2s ease;
            line-height: 1;
            /* Keeps bullet perfectly centered */
            display: inline-block;
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
                $rolePrefix = strtolower(Auth::user()->role->role_name) . '.';

                $categoryIcons = [
                    'Dashboard' => 'bi-grid-1x2',
                    'User Management' => 'bi-person',
                    'Restaurant' => 'bi-shop',
                    'System Configuration' => 'bi-gear',
                ];
            @endphp

            @foreach ($menuCategories as $category)
                @php
                    $visiblePages = [];
                    $isCategoryActive = false;

                    foreach ($category->pages as $page) {
                        $routeName = $page->route_name;

                        // Swap admin routes to manager routes
                        if (Auth::user()->role_id != 1 && str_starts_with((string) $routeName, 'admin.')) {
                            $routeName = str_replace('admin.', $rolePrefix, $routeName);
                        }

                        // Check if route exists
                        if ($routeName && \Illuminate\Support\Facades\Route::has($routeName)) {
                            $visiblePages[] = [
                                'page' => $page,
                                'routeName' => $routeName,
                            ];

                            // Check active state
                            $routeParts = explode('.', $routeName);
                            if (count($routeParts) >= 2) {
                                $activePattern = $routeParts[0] . '.' . $routeParts[1] . '.*';
                                if (request()->routeIs($activePattern) || request()->routeIs($routeName)) {
                                    $isCategoryActive = true;
                                }
                            }
                        }
                    }
                @endphp

                @if (count($visiblePages) > 0)
                    @php
                        $catIcon = $categoryIcons[$category->category_name] ?? 'bi-folder';
                        $collapseId = 'collapse-mgr-cat-' . $category->category_id;
                    @endphp

                    <!-- Collapsible Category Header -->
                    <button class="menu-header {{ $isCategoryActive ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                        data-bs-target="#{{ $collapseId }}"
                        aria-expanded="{{ $isCategoryActive ? 'true' : 'false' }}">
                        <div>
                            <i class="bi {{ $catIcon }} me-2" style="font-size: 1.1rem;"></i>
                            {{ $category->category_name }}
                        </div>
                        <i class="bi bi-chevron-down arrow-icon"></i>
                    </button>

                    <!-- Submenu Pages -->
                    <div class="collapse {{ $isCategoryActive ? 'show' : '' }}" id="{{ $collapseId }}">
                        <div class="submenu">
                            @foreach ($visiblePages as $item)
                                @php
                                    $page = $item['page'];
                                    $routeName = $item['routeName'];
                                    $routeUrl = route($routeName);

                                    $isActive = false;
                                    $routeParts = explode('.', $routeName);
                                    if (count($routeParts) >= 2) {
                                        $activePattern = $routeParts[0] . '.' . $routeParts[1] . '.*';
                                        $isActive =
                                            request()->routeIs($activePattern) || request()->routeIs($routeName);
                                    }
                                @endphp

                                <a href="{{ $routeUrl }}" class="{{ $isActive ? 'active' : '' }}">
                                    <span class="bullet">&bull;</span> {{ $page->page_name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            <!-- Logout Button -->
            <a href="#" id="sidebarLogoutBtn" class="text-danger"
                style="margin-top: 20px; padding: 15px 25px; display: block; text-decoration: none;">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
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
            },
            statusCode: {
                419: function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Session Expired',
                        text: 'Your session has expired. Please refresh and try again.',
                        confirmButtonText: 'Refresh Page'
                    }).then(() => {
                        window.location.reload();
                    });
                }
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
