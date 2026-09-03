<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - Restaurant Admin</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- SweetAlert -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        /* ============================================================
           GLOBAL
        ============================================================ */
        * {
            font-family: 'Inter', sans-serif;
        }

        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1a1d29;
            --sidebar-hover: #2d3148;
            --sidebar-active: #2d3148;
            --primary-color: #4f46e5;
            --primary-light: #818cf8;
        }

        body {
            background-color: #f0f2f5;
            overflow-x: hidden;
        }

        /* ============================================================
           SIDEBAR
        ============================================================ */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
        }

        /* ============================================================
           SIDEBAR BRAND
        ============================================================ */
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

        /* ============================================================
           SIDEBAR MENU
        ============================================================ */
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

        /* ============================================================
           NORMAL SIDEBAR LINKS
        ============================================================ */
        .sidebar-menu>a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            cursor: pointer;
        }

        .sidebar-menu>a:hover,
        .sidebar-menu>a.active {
            background: var(--sidebar-hover);
            color: #fff;
            border-left-color: var(--primary-color);
        }

        .sidebar-menu>a i {
            font-size: 1.1rem;
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        /* ============================================================
           MAIN CONTENT
        ============================================================ */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* ============================================================
           TOP NAVBAR
        ============================================================ */
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

        /* ============================================================
           USER AVATAR
        ============================================================ */
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

        /* ============================================================
           CONTENT AREA
        ============================================================ */
        .content-area {
            padding: 30px;
        }

        /* ============================================================
           STAT CARDS
        ============================================================ */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a1d29;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .bg-primary-light {
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
        }

        .bg-success-light {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .bg-warning-light {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .bg-info-light {
            background: rgba(6, 182, 212, 0.1);
            color: #06b6d4;
        }

        /* ============================================================
           TABLE
        ============================================================ */
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

        /* ============================================================
           BADGES
        ============================================================ */
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

        /* ============================================================
           FORM STYLE
        ============================================================ */
        .form-control,
        .form-select {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1.5px solid #e5e7eb;
            font-size: 0.9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .input-group-text {
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            border-right: none;
            border-radius: 8px 0 0 8px;
        }

        .input-group .form-control {
            border-left: none;
        }

        /* ============================================================
           LOADING SPINNER
        ============================================================ */
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

        /* ============================================================
           MODAL
        ============================================================ */
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

        /* ============================================================
           TABLE DROPDOWN FIX
        ============================================================ */
        .table-responsive,
        .custom-table {
            overflow: visible !important;
        }

        .dropdown-menu {
            z-index: 1055 !important;
            min-width: 180px;
            padding: 8px;
            border: 1px solid #e5e7eb !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1) !important;
            border-radius: 12px !important;
        }

        .dropdown-item {
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: #f3f4f6;
        }

        /* ============================================================
           RESPONSIVE
        ============================================================ */
        @media (max-width: 768px) {
            :root {
                --sidebar-width: 220px;
            }

            .top-navbar {
                padding: 15px 20px;
            }

            .content-area {
                padding: 20px;
            }

            .sidebar-brand {
                padding: 20px;
            }

            .sidebar-menu>a {
                padding-left: 20px;
                padding-right: 20px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

    <!-- ============================================================
         SIDEBAR
    ============================================================ -->
    <div class="sidebar">

        <!-- Brand -->
        <div class="sidebar-brand">
            <h4>
                <i class="bi bi-shop me-2"></i><span>Restaurant</span> Admin
            </h4>
        </div>

        <!-- Menu -->
        <div class="sidebar-menu">
            @php
                // Get the dynamic menu from the database
                $menuCategories = Auth::user()->getNavigationMenu();

                // Map icons to specific page codes (Fallback is bi-file-earmark)
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
            @endphp

            @foreach ($menuCategories as $category)
                @if ($category->pages && $category->pages->count() > 0)
                    <!-- Category Name -->
                    <div class="menu-label">{{ $category->category_name }}</div>

                    <!-- Pages under this category -->
                    @foreach ($category->pages as $page)
                        @php
                            // Check if the route exists to prevent crashes
                            $hasRoute = $page->route_name && \Illuminate\Support\Facades\Route::has($page->route_name);
                            $routeUrl = $hasRoute ? route($page->route_name) : '#';

                            // Check if this page is currently active
                            $isActive = false;
                            if ($hasRoute) {
                                $parts = explode('.', $page->route_name);
                                if (count($parts) >= 2) {
                                    $activePattern = $parts[0] . '.' . $parts[1] . '.*';
                                    $isActive =
                                        request()->routeIs($activePattern) || request()->routeIs($page->route_name);
                                } else {
                                    $isActive = request()->routeIs($page->route_name);
                                }
                            }

                            // Get icon
                            $icon = $pageIcons[$page->page_code] ?? 'bi-file-earmark';
                        @endphp

                        <a href="{{ $routeUrl }}" class="{{ $isActive ? 'active' : '' }}">
                            <i class="bi {{ $icon }}"></i> {{ $page->page_name }}
                        </a>
                    @endforeach
                @endif
            @endforeach

            <!-- Logout Button in Sidebar -->
            <div class="menu-label" style="margin-top: 20px;">Account</div>
            <a href="#" id="sidebarLogoutBtn" class="text-danger">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>

    <!-- ============================================================
         MAIN CONTENT
    ============================================================ -->
    <div class="main-content">

        <!-- ========================================================
             TOP NAVBAR
        ======================================================== -->
        <div class="top-navbar">

            <!-- Page Title -->
            <div>
                <span class="page-title">
                    @yield('page-title', 'Dashboard')
                </span>
            </div>

            <!-- USER DROPDOWN -->
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">

                    <button class="btn dropdown-toggle d-flex align-items-center gap-2"
                        style="border:none; background:transparent;" data-bs-toggle="dropdown">

                        <!-- User Avatar -->
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->user_name, 0, 1)) }}
                        </div>

                        <!-- User Information -->
                        <div class="text-start">
                            <div style="font-weight: 600; font-size: 0.85rem;">
                                {{ Auth::user()->user_name }}
                            </div>
                            <div style="font-size: 0.75rem; color: #6b7280;">
                                {{ Auth::user()->role->role_name ?? 'N/A' }}
                            </div>
                        </div>
                    </button>

                    <!-- Dropdown Menu -->
                    <ul class="dropdown-menu dropdown-menu-end">

                        <!-- Profile (Role-Aware) -->
                        @php
                            $currentRole = Auth::user()->role->role_name ?? '';
                            $profileRoute = match ($currentRole) {
                                'Manager' => \Illuminate\Support\Facades\Route::has('manager.users.index')
                                    ? route('manager.users.index')
                                    : '#',
                                'Admin' => \Illuminate\Support\Facades\Route::has('admin.users.index')
                                    ? route('admin.users.index')
                                    : '#',
                                default => '#',
                            };
                        @endphp

                        <li>
                            <a class="dropdown-item"
                                href="{{ $profileRoute }}?action=view&id={{ Auth::user()->user_id }}">
                                <i class="bi bi-person me-2"></i>
                                Profile
                            </a>
                        </li>

                        <!-- Divider -->
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <!-- Logout -->
                        <li>
                            <a class="dropdown-item text-danger" href="#" id="logoutBtn">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </a>

                            <!-- Logout Form -->
                            <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ========================================================
             CONTENT
        ======================================================== -->
        <div class="content-area">
            @yield('content')
        </div>
    </div>

    <!-- ============================================================
         JAVASCRIPT LIBRARIES
    ============================================================ -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        /* ============================================================
                       CSRF TOKEN
                    ============================================================ */
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        /* ============================================================
           GLOBAL SWEETALERT TOAST
        ============================================================ */
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });


        /* ============================================================
           SHOW TOAST
        ============================================================ */
        function showToast(icon, title) {
            Toast.fire({
                icon: icon,
                title: title
            });
        }


        /* ============================================================
           SHOW SUCCESS
        ============================================================ */
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: message,
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'OK'
            });
        }


        /* ============================================================
           SHOW ERROR
        ============================================================ */
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: message,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
        }


        /* ============================================================
           CONFIRM ACTION
        ============================================================ */
        function confirmAction(title, text, confirmText = 'Yes, do it!') {
            return Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancel'
            });
        }


        /* ============================================================
           LOGOUT CONFIRMATION (Both Top Nav & Sidebar)
        ============================================================ */
        $('#logoutBtn, #sidebarLogoutBtn').on('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Logout?',
                text: 'Are you sure you want to logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Logout',
                cancelButtonText: 'Cancel'
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
