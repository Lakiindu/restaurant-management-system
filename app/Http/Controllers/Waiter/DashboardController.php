<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('role');

        // Placeholder data (We will connect these to real Orders/Tables databases later!)
        $data = [
            'user' => $user,
            'active_tables' => 0,    // Tables currently served by this waiter
            'pending_orders' => 0,   // Orders sent to kitchen but not ready
            'completed_orders' => 0, // Orders finished today
            'total_sales' => 0.00,   // Waiter's total sales today
        ];

        return view('waiter.dashboard', $data);
    }
}
