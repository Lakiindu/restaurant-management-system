@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

    <!-- Welcome Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div style="background: linear-gradient(135deg, #4f46e5, #6366f1);
                        border-radius: 16px; padding: 30px 35px; color: #fff;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 style="font-weight: 700; margin-bottom: 5px;">
                            Welcome back, {{ Auth::user()->user_name }}! 👋
                        </h4>
                        <p style="opacity: 0.8; margin-bottom: 0;">
                            Here's what's happening with your restaurant today.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span style="font-size: 0.9rem; opacity: 0.7;">
                            <i class="bi bi-calendar3 me-1"></i>
                            {{ now()->format('l, F j, Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Users</div>
                        <div class="stat-number">{{ $totalUsers }}</div>
                    </div>
                    <div class="stat-icon bg-primary-light">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Active Roles</div>
                        <div class="stat-number">{{ $totalRoles }}</div>
                    </div>
                    <div class="stat-icon bg-success-light">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Pages</div>
                        <div class="stat-number">{{ $totalPages }}</div>
                    </div>
                    <div class="stat-icon bg-warning-light">
                        <i class="bi bi-file-earmark-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Categories</div>
                        <div class="stat-number">{{ $totalCategories }}</div>
                    </div>
                    <div class="stat-icon bg-info-light">
                        <i class="bi bi-folder-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="custom-table">
                <div class="table-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 style="font-weight: 600; margin-bottom: 2px;">Recent Users</h6>
                        <small style="color: #6b7280;">Latest registered users</small>
                    </div>
                    <a href="#" class="btn btn-sm"
                       style="background: var(--primary-color); color: #fff; border-radius: 8px;">
                        <i class="bi bi-plus-lg me-1"></i> Add User
                    </a>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers as $index => $user)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar" style="width:30px; height:30px; font-size:0.7rem;">
                                            {{ strtoupper(substr($user->user_name, 0, 1)) }}
                                        </div>
                                        {{ $user->user_name }}
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary"
                                          style="border-radius: 8px; padding: 5px 10px;">
                                        {{ $user->role->role_name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->status == 1)
                                        <span class="badge-active">Active</span>
                                    @else
                                        <span class="badge-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No users found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection