<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Page;
use App\Models\PageCategory;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'totalUsers' => User::where('status', 1)->count(),
            'totalRoles' => Role::where('status', 1)->count(),
            'totalPages' => Page::where('status', 1)->count(),
            'totalCategories' => PageCategory::where('status', 1)->count(),
            'recentUsers' => User::with('role')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return view('admin.dashboard', $data);
    }
}