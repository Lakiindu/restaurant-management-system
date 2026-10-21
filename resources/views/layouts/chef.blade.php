<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Chef Panel') - Restaurant POS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1e1e2d;
            --sidebar-hover: #2b2b40;
            --primary-color: #117854;
            --primary-light: #d1fae5;
        }

        body {
            background-color: #f7f7f9;
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-brand h5 {
            color: #fff;
            margin: 0;
            font-weight: 700;
        }

        .sidebar-brand span {
            color: var(--primary-light);
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .menu-header {
            padding: 12px 25px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .submenu a {
            display: flex;
            align-items: center;
            padding: 10px 25px 10px 35px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            text-decoration: none;
            transition: 0.2s;
        }

        .submenu a:hover,
        .submenu a.active {
            color: #fff;
            background: var(--sidebar-hover);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .top-navbar {
            background: #fff;
            padding: 15px 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e1e2d;
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
        }

        .content-area {
            padding: 25px;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <h5><i class="bi bi-shop me-2"></i><span>Chef</span> Panel</h5>
        </div>
        <div class="sidebar-menu">
            @php $menuCategories = Auth::user()->getNavigationMenu(); @endphp
            @foreach ($menuCategories as $category)
                <div class="menu-header"><span>{{ $category->category_name }}</span></div>
                <div class="submenu">
                    @foreach ($category->pages as $page)
                        @php
                            $routeName = str_replace('admin.', 'chef.', $page->route_name);
                            $routeUrl = \Illuminate\Support\Facades\Route::has($routeName) ? route($routeName) : '#';
                            $isActive = request()->routeIs($routeName);
                        @endphp
                        <a href="{{ $routeUrl }}" class="{{ $isActive ? 'active' : '' }}">
                            <i class="bi bi-circle-fill me-2" style="font-size: 0.4rem;"></i> {{ $page->page_name }}
                        </a>
                    @endforeach
                </div>
            @endforeach
            <a href="#" id="trigger-logout" class="text-danger mt-4 p-3 d-block text-decoration-none border-top"
                style="border-color: rgba(255,255,255,0.05)!important;">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
            <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </div>
    </div>

    <div class="main-content">
        <div class="top-navbar">
            <div class="page-title">@yield('page-title', 'Dashboard')</div>
            <div class="d-flex align-items-center gap-3">
                <div class="user-avatar">{{ strtoupper(substr(Auth::user()->user_name ?? 'C', 0, 1)) }}</div>
                <div>
                    <div class="fw-bold fs-8 text-dark">{{ Auth::user()->user_name ?? 'Chef' }}</div>
                    <div class="text-muted fs-9">{{ Auth::user()->role->role_name ?? 'Chef' }}</div>
                </div>
            </div>
        </div>
        <div class="content-area">
            @yield('content')
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $('#trigger-logout').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                    title: 'Logout?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, logout'
                })
                .then((res) => {
                    if (res.isConfirmed) $('#logoutForm').submit();
                });
        });

        function showSuccess(msg) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: msg,
                confirmButtonColor: '#117854'
            });
        }

        function showError(msg) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: msg,
                confirmButtonColor: '#d33'
            });
        }
    </script>
    @yield('scripts')
</body>

</html>
