<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;

// ========================================
// Root Redirect
// ========================================
Route::get('/', function () {
    return redirect()->route('login');
});

// ========================================
// Login Routes
// ========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});

// ========================================
// Logout
// ========================================
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ========================================
// Admin Routes
// ========================================
Route::middleware(['auth', 'role:Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

// ========================================
// Other role dashboards (placeholders)
// ========================================
Route::middleware(['auth', 'role:Manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', fn() => 'Manager Dashboard - Coming Soon')->name('dashboard');
});

Route::middleware(['auth', 'role:Chef'])->prefix('chef')->name('chef.')->group(function () {
    Route::get('/dashboard', fn() => 'Chef Dashboard - Coming Soon')->name('dashboard');
});

Route::middleware(['auth', 'role:Waiter'])->prefix('waiter')->name('waiter.')->group(function () {
    Route::get('/dashboard', fn() => 'Waiter Dashboard - Coming Soon')->name('dashboard');
});

Route::middleware(['auth', 'role:Cashier'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/dashboard', fn() => 'Cashier Dashboard - Coming Soon')->name('dashboard');
});