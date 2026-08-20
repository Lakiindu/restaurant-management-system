<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\RoleOptionPermission;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('role');

        // Get pages this user can access
        $allowedPages = RolePermission::where('role_id', $user->role_id)
            ->where('allow', 1)
            ->pluck('page_code')
            ->toArray();

        // Get options this user can access
        $allowedOptions = RoleOptionPermission::where('roles_id', $user->role_id)
            ->where('allow', 1)
            ->pluck('option_code')
            ->toArray();

        $data = [
            'user' => $user,
            'allowedPages' => $allowedPages,
            'allowedOptions' => $allowedOptions,
            'totalUsers' => User::where('status', 1)->count(),
        ];

        return view('manager.dashboard', $data);
    }
}
