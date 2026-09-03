<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('role');

        $data = [
            'user' => $user,
            'totalUsers' => User::count(),
            'activeUsers' => User::where('status', 1)->count(),
            'inactiveUsers' => User::where('status', 0)->count(),
            'totalRoles' => Role::where('status', 1)->count(),
            'recentUsers' => User::with('role')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'canAddUser' => $user->hasOptionPermission('USER_ADD'),
            'canViewUsers' => $user->hasPagePermission('USER_LIST'),
        ];

        return view('manager.dashboard', $data);
    }
}
