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
                            Welcome, {{ $user->user_name }}! 👋
                        </h4>
                        <p style="opacity: 0.9; margin-bottom: 0;">
                            You are logged in as <strong>{{ $user->role->role_name }}</strong>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span style="font-size: 0.9rem; opacity: 0.8;">
                            <i class="bi bi-calendar3 me-1"></i>
                            {{ now()->format('l, F j, Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="alert" style="background: #eff6ff; border: 1px solid #3b82f6; border-radius: 12px; color: #1e40af;">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>Testing Mode:</strong> This page shows what permissions the Admin assigned to you.
        This helps verify that the permission system works correctly.
    </div>

    <!-- Permissions Test -->
    <div class="row">
        <!-- Pages you can access -->
        <div class="col-lg-6">
            <div class="permission-card">
                <h6 style="font-weight: 700; margin-bottom: 15px; color: #1a1d29;">
                    <i class="bi bi-file-earmark-check-fill me-2" style="color: #10b981;"></i>
                    Pages You Can Access
                </h6>
                @if (count($allowedPages) > 0)
                    @foreach ($allowedPages as $page)
                        <span class="permission-badge">
                            <i class="bi bi-check-circle me-1"></i>{{ $page }}
                        </span>
                    @endforeach
                @else
                    <span class="permission-badge no-permission">
                        <i class="bi bi-x-circle me-1"></i>No pages assigned
                    </span>
                @endif
            </div>
        </div>

        <!-- Actions you can perform -->
        <div class="col-lg-6">
            <div class="permission-card">
                <h6 style="font-weight: 700; margin-bottom: 15px; color: #1a1d29;">
                    <i class="bi bi-lightning-charge-fill me-2" style="color: #f59e0b;"></i>
                    Actions You Can Perform
                </h6>
                @if (count($allowedOptions) > 0)
                    @foreach ($allowedOptions as $option)
                        <span class="permission-badge">
                            <i class="bi bi-lightning me-1"></i>{{ $option }}
                        </span>
                    @endforeach
                @else
                    <span class="permission-badge no-permission">
                        <i class="bi bi-x-circle me-1"></i>No actions assigned
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Testing Instructions -->
    <div class="row mt-4">
        <div class="col-12">
            <div style="background: #fff; border-radius: 12px; padding: 25px; border: 1px solid #e5e7eb;">
                <h6 style="font-weight: 700; color: #1a1d29;">
                    <i class="bi bi-clipboard-check me-2"></i>Testing Checklist
                </h6>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6 style="color: #10b981;">✅ What Should Work:</h6>
                        <ul style="color: #6b7280; font-size: 0.9rem;">
                            <li>See Users menu in sidebar (if Admin allowed)</li>
                            <li>Access Users page</li>
                            <li>See only allowed action buttons</li>
                            <li>Add/Edit users (if allowed)</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 style="color: #ef4444;">❌ What Should Be Blocked:</h6>
                        <ul style="color: #6b7280; font-size: 0.9rem;">
                            <li>Cannot see Admin's Roles menu</li>
                            <li>Cannot see Admin's Permissions menu</li>
                            <li>Cannot access URLs like <code>/admin/dashboard</code></li>
                            <li>Delete button hidden (if not allowed)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Links -->
    <div class="row mt-4">
        <div class="col-12">
            <div style="background: #fff; border-radius: 12px; padding: 25px; border: 1px solid #e5e7eb;">
                <h6 style="font-weight: 700; color: #1a1d29;">
                    <i class="bi bi-link-45deg me-2"></i>Try These URLs to Test Blocking
                </h6>
                <hr>
                <p style="color: #6b7280; font-size: 0.9rem;">Click these links — you should get "403 Access Denied":</p>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ url('/admin/dashboard') }}" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-shield-x"></i> Try Admin Dashboard
                    </a>
                    <a href="{{ url('/admin/users') }}" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-shield-x"></i> Try Admin Users
                    </a>
                    <a href="{{ url('/admin/roles') }}" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-shield-x"></i> Try Admin Roles
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection
