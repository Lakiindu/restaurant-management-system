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
           COLLAPSIBLE USER MANAGEMENT
        ============================================================ */

        .menu-dropdown {
            width: 100%;
        }

        /* Parent button */

        .menu-dropdown-toggle {
            width: 100%;

            display: flex;
            align-items: center;

            padding: 12px 25px;

            background: transparent;
            border: none;

            color: rgba(255, 255, 255, 0.7);

            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;

            text-align: left;

            cursor: pointer;

            transition: all 0.2s ease;

            border-left: 3px solid transparent;
        }

        .menu-dropdown-toggle:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        /* Parent icon */

        .menu-dropdown-toggle .menu-icon {
            font-size: 1.1rem;
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        /* Parent text */

        .menu-dropdown-toggle .menu-text {
            flex: 1;
        }

        /* Chevron */

        .menu-dropdown-toggle .menu-arrow {
            font-size: 0.85rem;
            transition: transform 0.25s ease;
        }

        /* Rotate arrow when open */

        .menu-dropdown-toggle.open .menu-arrow {
            transform: rotate(180deg);
        }

        /* Active parent */

        .menu-dropdown-toggle.active {
            background: var(--sidebar-hover);
            color: #fff;
            border-left-color: var(--primary-color);
        }


        /* ============================================================
           USER MANAGEMENT SUBMENU
        ============================================================ */

        .submenu {
            display: none;

            padding: 3px 0 5px 0;

            background: rgba(0, 0, 0, 0.08);
        }

        .submenu.show {
            display: block;
        }

        /* Submenu links */

        .submenu a {
            position: relative;

            display: flex;
            align-items: center;

            padding: 10px 20px 10px 53px;

            color: rgba(255, 255, 255, 0.58);

            text-decoration: none;

            font-size: 0.88rem;

            transition: all 0.2s ease;
        }

        /* Bullet */

        .submenu a::before {
            content: '';

            position: absolute;

            left: 24px;

            width: 5px;
            height: 5px;

            border-radius: 50%;

            background: rgba(255, 255, 255, 0.45);

            transition: all 0.2s ease;
        }

        /* Hover */

        .submenu a:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.04);
        }

        .submenu a:hover::before {
            background: var(--primary-light);
            transform: scale(1.25);
        }

        /* Active submenu */

        .submenu a.active {
            color: #fff;
            background: rgba(79, 70, 229, 0.12);
        }

        .submenu a.active::before {
            background: var(--primary-light);
            transform: scale(1.25);
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

            .menu-dropdown-toggle,
            .sidebar-menu>a {
                padding-left: 20px;
                padding-right: 20px;
            }

            .submenu a {
                padding-left: 48px;
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

        <!-- BRAND -->
        <div class="sidebar-brand">

            <h4>
                <i class="bi bi-shop"></i>
                <span>Restaurant</span> Admin
            </h4>

        </div>


        <!-- SIDEBAR MENU -->
        <div class="sidebar-menu">


            <!-- ====================================================
                 MAIN
            ==================================================== -->

            <div class="menu-label">
                Main
            </div>


            <!-- Dashboard -->

            <a href="{{ route('admin.dashboard') }}"
                class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">

                <i class="bi bi-grid-1x2-fill"></i>

                Dashboard

            </a>


            <!-- ====================================================
                 USER MANAGEMENT
            ==================================================== -->

            <div class="menu-label">
                User Management
            </div>


            @php
                $userManagementActive =
                    request()->routeIs('admin.users.*') ||
                    request()->routeIs('admin.roles.*') ||
                    request()->routeIs('admin.page-categories.*') ||
                    request()->routeIs('admin.pages.*') ||
                    request()->routeIs('admin.role-options.*') ||
                    request()->routeIs('admin.permissions.*');
            @endphp


            <!-- User Management Parent -->

            <div class="menu-dropdown">

                <button type="button" class="menu-dropdown-toggle {{ $userManagementActive ? 'active open' : '' }}"
                    id="userManagementToggle">

                    <i class="bi bi-person-fill-gear menu-icon"></i>

                    <span class="menu-text">
                        User Management
                    </span>

                    <i class="bi bi-chevron-down menu-arrow"></i>

                </button>


                <!-- ====================================================
                     SUBMENU
                ==================================================== -->

                <div class="submenu {{ $userManagementActive ? 'show' : '' }}" id="userManagementSubmenu">


                    <!-- Users -->

                    <a href="{{ route('admin.users.index') }}"
                        class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">

                        Users

                    </a>


                    <!-- Roles -->

                    <a href="{{ route('admin.roles.index') }}"
                        class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">

                        Roles

                    </a>


                    <!-- Page Categories -->

                    <a href="{{ route('admin.page-categories.index') }}"
                        class="{{ request()->routeIs('admin.page-categories.*') ? 'active' : '' }}">

                        Page Categories

                    </a>


                    <!-- Pages -->

                    <a href="{{ route('admin.pages.index') }}"
                        class="{{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">

                        Pages

                    </a>


                    <!-- Page Options -->

                    <a href="{{ route('admin.role-options.index') }}"
                        class="{{ request()->routeIs('admin.role-options.*') ? 'active' : '' }}">

                        Page Options

                    </a>


                    <!-- Permissions -->

                    <a href="{{ route('admin.permissions.index') }}"
                        class="{{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">

                        Permissions

                    </a>

                </div>

            </div>


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


            <!-- ====================================================
                 USER DROPDOWN
            ==================================================== -->

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

                                {{ Auth::user()->role->role_name }}

                            </div>

                        </div>


                    </button>


                    <!-- Dropdown Menu -->

                    <ul class="dropdown-menu dropdown-menu-end">


                        <!-- Profile -->

                        <li>

                            <a class="dropdown-item"
                                href="{{ route('admin.users.index') }}?action=view&id={{ Auth::user()->user_id }}">

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
           COLLAPSIBLE USER MANAGEMENT MENU
        ============================================================ */

        document.addEventListener('DOMContentLoaded', function() {

            const toggle =
                document.getElementById('userManagementToggle');

            const submenu =
                document.getElementById('userManagementSubmenu');


            if (!toggle || !submenu) {
                return;
            }


            toggle.addEventListener('click', function() {

                /*
                 * Toggle submenu visibility
                 */

                submenu.classList.toggle('show');


                /*
                 * Toggle arrow rotation
                 */

                toggle.classList.toggle('open');

            });

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

                toast.onmouseenter =
                    Swal.stopTimer;

                toast.onmouseleave =
                    Swal.resumeTimer;

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

        function confirmAction(
            title,
            text,
            confirmText = 'Yes, do it!'
        ) {

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
           LOGOUT CONFIRMATION
        ============================================================ */

        $('#logoutBtn').on('click', function(e) {

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
