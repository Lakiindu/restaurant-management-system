@extends('layouts.manager')

@section('title', 'Dashboard')
@section('page-title', 'Manager Dashboard')

@section('content')

    <!-- Welcome Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div
                style="background: linear-gradient(135deg, #10b981, #059669);
                        border-radius: 16px; padding: 30px 35px; color: #fff;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 style="font-weight: 700; margin-bottom: 5px;">
                            Welcome back, {{ $user->user_name }}! 👋
                        </h4>
                        <p style="opacity: 0.9; margin-bottom: 0;">
                            Here's an overview of your restaurant management panel.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div style="font-size: 0.9rem; opacity: 0.85;">
                            <i class="bi bi-calendar3 me-1"></i>
                            {{ now()->format('l, F j, Y') }}
                        </div>
                        <div style="font-size: 0.85rem; opacity: 0.8; margin-top: 4px;">
                            Role: <strong>{{ $user->role->role_name }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div style="color:#6b7280; font-size:0.85rem; font-weight:500;">Total Users</div>
                        <div style="font-size:1.8rem; font-weight:700; color:#1a1d29;">{{ $totalUsers }}</div>
                    </div>
                    <div
                        style="width:48px;height:48px;border-radius:12px;background:rgba(16,185,129,0.1);color:#10b981;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div style="color:#6b7280; font-size:0.85rem; font-weight:500;">Active Users</div>
                        <div style="font-size:1.8rem; font-weight:700; color:#1a1d29;">{{ $activeUsers }}</div>
                    </div>
                    <div
                        style="width:48px;height:48px;border-radius:12px;background:rgba(59,130,246,0.1);color:#3b82f6;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div style="color:#6b7280; font-size:0.85rem; font-weight:500;">Inactive Users</div>
                        <div style="font-size:1.8rem; font-weight:700; color:#1a1d29;">{{ $inactiveUsers }}</div>
                    </div>
                    <div
                        style="width:48px;height:48px;border-radius:12px;background:rgba(239,68,68,0.1);color:#ef4444;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div style="color:#6b7280; font-size:0.85rem; font-weight:500;">Active Roles</div>
                        <div style="font-size:1.8rem; font-weight:700; color:#1a1d29;">{{ $totalRoles }}</div>
                    </div>
                    <div
                        style="width:48px;height:48px;border-radius:12px;background:rgba(245,158,11,0.1);color:#f59e0b;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div style="background:#fff; border-radius:12px; border:1px solid #e5e7eb; padding:25px; height:100%;">
                <h6 style="font-weight:700; margin-bottom:15px; color:#1a1d29;">
                    <i class="bi bi-lightning-charge-fill me-2 text-success"></i>Quick Actions
                </h6>

                <div class="d-grid gap-2">
                    @if ($canViewUsers)
                        <a href="{{ route('manager.users.index') }}" class="btn btn-outline-success"
                            style="border-radius:10px; text-align:left; padding:12px 15px;">
                            <i class="bi bi-people me-2"></i> Manage Users
                        </a>
                    @endif

                    @if ($canAddUser)
                        <a href="{{ route('manager.users.index') }}" class="btn btn-success"
                            style="border-radius:10px; text-align:left; padding:12px 15px;">
                            <i class="bi bi-person-plus me-2"></i> Add New User
                        </a>
                    @endif

                    <button type="button" class="btn btn-light"
                        style="border-radius:10px; text-align:left; padding:12px 15px;" disabled>
                        <i class="bi bi-cart me-2"></i> Orders <small class="text-muted">(Coming Soon)</small>
                    </button>

                    <button type="button" class="btn btn-light"
                        style="border-radius:10px; text-align:left; padding:12px 15px;" disabled>
                        <i class="bi bi-book me-2"></i> Menu <small class="text-muted">(Coming Soon)</small>
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="col-lg-8 mb-4">
            <div style="background:#fff; border-radius:12px; border:1px solid #e5e7eb; overflow:hidden; height:100%;">
                <div style="padding:20px 25px; border-bottom:1px solid #f0f0f0;"
                    class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 style="font-weight:700; margin-bottom:2px; color:#1a1d29;">Recent Users</h6>
                        <small style="color:#6b7280;">Latest accounts in the system</small>
                    </div>
                    @if ($canViewUsers)
                        <a href="{{ route('manager.users.index') }}" class="btn btn-sm btn-outline-success"
                            style="border-radius:8px;">
                            View All
                        </a>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th
                                    style="background:#f9fafb; font-size:0.8rem; color:#6b7280; padding:12px 20px; border:none;">
                                    User</th>
                                <th
                                    style="background:#f9fafb; font-size:0.8rem; color:#6b7280; padding:12px 20px; border:none;">
                                    Role</th>
                                <th
                                    style="background:#f9fafb; font-size:0.8rem; color:#6b7280; padding:12px 20px; border:none;">
                                    Status</th>
                                <th
                                    style="background:#f9fafb; font-size:0.8rem; color:#6b7280; padding:12px 20px; border:none;">
                                    Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers as $recent)
                                <tr>
                                    <td style="padding:14px 20px; vertical-align:middle;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div
                                                style="width:34px;height:34px;border-radius:50%;background:#10b981;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:0.8rem;">
                                                {{ strtoupper(substr($recent->user_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight:600;">{{ $recent->user_name }}</div>
                                                <small style="color:#6b7280;">{{ $recent->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding:14px 20px; vertical-align:middle;">
                                        <span class="badge"
                                            style="background:rgba(16,185,129,0.1); color:#10b981; border-radius:8px; padding:6px 10px;">
                                            {{ $recent->role->role_name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td style="padding:14px 20px; vertical-align:middle;">
                                        @if ($recent->status == 1)
                                            <span
                                                style="background:rgba(16,185,129,0.1);color:#10b981;padding:5px 12px;border-radius:20px;font-size:0.78rem;font-weight:500;">Active</span>
                                        @else
                                            <span
                                                style="background:rgba(239,68,68,0.1);color:#ef4444;padding:5px 12px;border-radius:20px;font-size:0.78rem;font-weight:500;">Inactive</span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 20px; vertical-align:middle; color:#6b7280;">
                                        {{ \Carbon\Carbon::parse($recent->created_at)->format('M d, Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No users found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
