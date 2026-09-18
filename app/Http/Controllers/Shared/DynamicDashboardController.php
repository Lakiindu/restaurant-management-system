<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class DynamicDashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('role');

        $roleName = $user->role->role_name ?? '';

        if ($roleName === 'Admin') {
            // Reuse existing admin dashboard data style if needed
            return app(\App\Http\Controllers\Admin\DashboardController::class)->index();
        }

        if ($roleName === 'Manager') {
            return app(\App\Http\Controllers\Manager\DashboardController::class)->index();
        }

        abort(403, 'No dashboard assigned for your role.');
    }
}
