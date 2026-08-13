<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    // Show Login Page
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    // Process Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)
                    ->where('status', 1)
                    ->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No active account found with this email.',
            ])->withInput();
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Incorrect password.',
            ])->withInput();
        }

        Auth::login($user, $request->filled('remember'));

        return $this->redirectByRole($user);
    }

    // Redirect user to their dashboard based on role
    private function redirectByRole(User $user)
    {
        $user->load('role');

        switch ($user->role->role_name) {
            case 'Admin':
                return redirect()->route('admin.dashboard');
            case 'Manager':
                return redirect()->route('manager.dashboard');
            case 'Chef':
                return redirect()->route('chef.dashboard');
            case 'Waiter':
                return redirect()->route('waiter.dashboard');
            case 'Cashier':
                return redirect()->route('cashier.dashboard');
            default:
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'email' => 'Your role has no dashboard assigned.',
                ]);
        }
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}