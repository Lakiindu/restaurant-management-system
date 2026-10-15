@extends('layouts.waiter')

@section('title', 'Dashboard')
@section('page-title', 'Waiter Dashboard')

@section('content')

    <!-- Welcome Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div
                style="background: linear-gradient(135deg, #0ea5e9, #0284c7);
                        border-radius: 16px; padding: 30px 35px; color: #fff; box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.2);">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 style="font-weight: 700; margin-bottom: 5px;">
                            Good shift, {{ $user->user_name }}! ☕
                        </h4>
                        <p style="opacity: 0.9; margin-bottom: 0;">
                            Ready to take some orders? Let's give great service today.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div style="font-size: 1rem; opacity: 0.9; font-weight: 500;">
                            <i class="bi bi-clock me-1"></i> <span id="liveClock"></span>
                        </div>
                        <div style="font-size: 0.85rem; opacity: 0.8; margin-top: 4px;">
                            {{ now()->format('l, F j, Y') }} | Role: <strong>{{ $user->role->role_name }}</strong>
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
                        <div style="color:#64748b; font-size:0.85rem; font-weight:500;">My Active Tables</div>
                        <div style="font-size:1.8rem; font-weight:700; color:#0f172a;">{{ $active_tables }}</div>
                    </div>
                    <div
                        style="width:48px;height:48px;border-radius:12px;background:rgba(14,165,233,0.1);color:#0ea5e9;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                        <i class="bi bi-ui-checks-grid"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div style="color:#64748b; font-size:0.85rem; font-weight:500;">Pending Orders</div>
                        <div style="font-size:1.8rem; font-weight:700; color:#0f172a;">{{ $pending_orders }}</div>
                    </div>
                    <div
                        style="width:48px;height:48px;border-radius:12px;background:rgba(245,158,11,0.1);color:#f59e0b;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div style="color:#64748b; font-size:0.85rem; font-weight:500;">Completed Today</div>
                        <div style="font-size:1.8rem; font-weight:700; color:#0f172a;">{{ $completed_orders }}</div>
                    </div>
                    <div
                        style="width:48px;height:48px;border-radius:12px;background:rgba(16,185,129,0.1);color:#10b981;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                        <i class="bi bi-check2-all"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div style="color:#64748b; font-size:0.85rem; font-weight:500;">My Sales</div>
                        <div style="font-size:1.8rem; font-weight:700; color:#0f172a;">Rs
                            {{ number_format($total_sales, 2) }}</div>
                    </div>
                    <div
                        style="width:48px;height:48px;border-radius:12px;background:rgba(139,92,246,0.1);color:#8b5cf6;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:25px; height:100%;">
                <h6 style="font-weight:700; margin-bottom:15px; color:#0f172a;">
                    <i class="bi bi-lightning-charge-fill me-2 text-warning"></i>Quick Actions
                </h6>

                <div class="d-grid gap-3">
                    <button type="button" class="btn"
                        style="background:#0ea5e9; color:#fff; border-radius:10px; text-align:left; padding:12px 15px;"
                        disabled>
                        <i class="bi bi-plus-circle me-2"></i> Take New Order <small style="float: right;">(Coming
                            Soon)</small>
                    </button>

                    <button type="button" class="btn btn-light"
                        style="border-radius:10px; text-align:left; padding:12px 15px; border: 1px solid #e2e8f0;" disabled>
                        <i class="bi bi-grid me-2"></i> View Floor Plan <small class="text-muted"
                            style="float: right;">(Coming Soon)</small>
                    </button>

                    <button type="button" class="btn btn-light"
                        style="border-radius:10px; text-align:left; padding:12px 15px; border: 1px solid #e2e8f0;" disabled>
                        <i class="bi bi-book me-2"></i> Browse Menu <small class="text-muted" style="float: right;">(Coming
                            Soon)</small>
                    </button>
                </div>
            </div>
        </div>

        <!-- Active Orders List -->
        <div class="col-lg-8 mb-4">
            <div style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden; height:100%;">
                <div style="padding:20px 25px; border-bottom:1px solid #f1f5f9;"
                    class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 style="font-weight:700; margin-bottom:2px; color:#0f172a;">My Active Orders</h6>
                        <small style="color:#64748b;">Orders currently being prepared or served</small>
                    </div>
                    <span class="badge" style="background: var(--primary-color); border-radius: 20px;">0 Active</span>
                </div>

                <div class="p-5 text-center">
                    <div style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <h6 style="color: #475569; font-weight: 600;">No active orders right now</h6>
                    <p style="color: #94a3b8; font-size: 0.9rem;">When you take a new order, it will appear here so you can
                        track its progress from the kitchen.</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Simple Live Clock Script
        setInterval(() => {
            const d = new Date();
            document.getElementById('liveClock').innerHTML = d.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }, 1000);
    </script>
@endpush
