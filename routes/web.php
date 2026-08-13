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

        // User Management (AJAX)
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/fetch', [\App\Http\Controllers\Admin\UserController::class, 'fetchUsers'])->name('users.fetch');
        Route::get('/users/{id}/get', [\App\Http\Controllers\Admin\UserController::class, 'getUser'])->name('users.get');
        Route::post('/users/store', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}/update', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}/delete', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{id}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Role Management (AJAX)
        Route::get('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/fetch', [\App\Http\Controllers\Admin\RoleController::class, 'fetchRoles'])->name('roles.fetch');
        Route::get('/roles/active', [\App\Http\Controllers\Admin\RoleController::class, 'getActiveRoles'])->name('roles.active');
        Route::get('/roles/{id}/get', [\App\Http\Controllers\Admin\RoleController::class, 'getRole'])->name('roles.get');
        Route::post('/roles/store', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{id}/update', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}/delete', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');
        Route::patch('/roles/{id}/toggle-status', [\App\Http\Controllers\Admin\RoleController::class, 'toggleStatus'])->name('roles.toggle-status');
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