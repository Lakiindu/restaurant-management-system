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

        // Permission Management (AJAX)
        Route::get('/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('permissions.index');
        Route::get('/permissions/roles', [\App\Http\Controllers\Admin\PermissionController::class, 'getRoles'])->name('permissions.roles');
        Route::get('/permissions/role/{roleId}', [\App\Http\Controllers\Admin\PermissionController::class, 'getPermissions'])->name('permissions.get');
        Route::post('/permissions/save', [\App\Http\Controllers\Admin\PermissionController::class, 'savePermissions'])->name('permissions.save');
        Route::post('/permissions/copy', [\App\Http\Controllers\Admin\PermissionController::class, 'copyPermissions'])->name('permissions.copy');
        Route::delete('/permissions/clear/{roleId}', [\App\Http\Controllers\Admin\PermissionController::class, 'clearPermissions'])->name('permissions.clear');

        // Page Categories Management (AJAX)
        Route::get('/page-categories', [\App\Http\Controllers\Admin\PageCategoryController::class, 'index'])->name('page-categories.index');
        Route::get('/page-categories/fetch', [\App\Http\Controllers\Admin\PageCategoryController::class, 'fetchCategories'])->name('page-categories.fetch');
        Route::get('/page-categories/active', [\App\Http\Controllers\Admin\PageCategoryController::class, 'getActiveCategories'])->name('page-categories.active');
        Route::get('/page-categories/{id}/get', [\App\Http\Controllers\Admin\PageCategoryController::class, 'getCategory'])->name('page-categories.get');
        Route::post('/page-categories/store', [\App\Http\Controllers\Admin\PageCategoryController::class, 'store'])->name('page-categories.store');
        Route::put('/page-categories/{id}/update', [\App\Http\Controllers\Admin\PageCategoryController::class, 'update'])->name('page-categories.update');
        Route::delete('/page-categories/{id}/delete', [\App\Http\Controllers\Admin\PageCategoryController::class, 'destroy'])->name('page-categories.destroy');
        Route::patch('/page-categories/{id}/toggle-status', [\App\Http\Controllers\Admin\PageCategoryController::class, 'toggleStatus'])->name('page-categories.toggle-status');

        // Pages Management (AJAX)
        Route::get('/pages', [\App\Http\Controllers\Admin\PageController::class, 'index'])->name('pages.index');
        Route::get('/pages/fetch', [\App\Http\Controllers\Admin\PageController::class, 'fetchPages'])->name('pages.fetch');
        Route::get('/pages/active', [\App\Http\Controllers\Admin\PageController::class, 'getActivePages'])->name('pages.active');
        Route::get('/pages/{id}/get', [\App\Http\Controllers\Admin\PageController::class, 'getPage'])->name('pages.get');
        Route::post('/pages/store', [\App\Http\Controllers\Admin\PageController::class, 'store'])->name('pages.store');
        Route::put('/pages/{id}/update', [\App\Http\Controllers\Admin\PageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{id}/delete', [\App\Http\Controllers\Admin\PageController::class, 'destroy'])->name('pages.destroy');
        Route::patch('/pages/{id}/toggle-status', [\App\Http\Controllers\Admin\PageController::class, 'toggleStatus'])->name('pages.toggle-status');

        // Role Options Management (AJAX)
        Route::get('/role-options', [\App\Http\Controllers\Admin\RoleOptionController::class, 'index'])->name('role-options.index');
        Route::get('/role-options/fetch', [\App\Http\Controllers\Admin\RoleOptionController::class, 'fetchOptions'])->name('role-options.fetch');
        Route::get('/role-options/{id}/get', [\App\Http\Controllers\Admin\RoleOptionController::class, 'getOption'])->name('role-options.get');
        Route::post('/role-options/store', [\App\Http\Controllers\Admin\RoleOptionController::class, 'store'])->name('role-options.store');
        Route::put('/role-options/{id}/update', [\App\Http\Controllers\Admin\RoleOptionController::class, 'update'])->name('role-options.update');
        Route::delete('/role-options/{id}/delete', [\App\Http\Controllers\Admin\RoleOptionController::class, 'destroy'])->name('role-options.destroy');
        Route::patch('/role-options/{id}/toggle-status', [\App\Http\Controllers\Admin\RoleOptionController::class, 'toggleStatus'])->name('role-options.toggle-status');
    });

// ========================================
// Manager Routes
// ========================================
Route::middleware(['auth', 'role:Manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Manager\DashboardController::class, 'index'])->name('dashboard');

        // Manager can access users if permitted (using same admin controller)
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/fetch', [\App\Http\Controllers\Admin\UserController::class, 'fetchUsers'])->name('users.fetch');
        Route::get('/users/{id}/get', [\App\Http\Controllers\Admin\UserController::class, 'getUser'])->name('users.get');
        Route::post('/users/store', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}/update', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}/delete', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{id}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::get('/roles/active', [\App\Http\Controllers\Admin\RoleController::class, 'getActiveRoles'])->name('roles.active');
    });

// ========================================
// Other role dashboards (placeholders)
// ========================================
Route::middleware(['auth', 'role:Chef'])->prefix('chef')->name('chef.')->group(function () {
    Route::get('/dashboard', fn() => 'Chef Dashboard - Coming Soon')->name('dashboard');
});

Route::middleware(['auth', 'role:Waiter'])->prefix('waiter')->name('waiter.')->group(function () {
    Route::get('/dashboard', fn() => 'Waiter Dashboard - Coming Soon')->name('dashboard');
});

Route::middleware(['auth', 'role:Cashier'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/dashboard', fn() => 'Cashier Dashboard - Coming Soon')->name('dashboard');
});
