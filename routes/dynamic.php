<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Dynamic Routes Loader
|--------------------------------------------------------------------------
| - Loads routes from pages table
| - Uses web middleware (session/auth)
| - If page is under admin/*, also creates manager/* alias
*/

try {
    if (
        Schema::hasTable('pages') &&
        Schema::hasColumn('pages', 'controller_name') &&
        Schema::hasColumn('pages', 'url_path')
    ) {
        $pages = DB::table('pages')
            ->where('status', 1)
            ->whereNotNull('controller_name')
            ->whereNotNull('url_path')
            ->whereNotNull('route_name')
            ->where('controller_name', '!=', '')
            ->where('url_path', '!=', '')
            ->where('route_name', '!=', '')
            ->get();

        Route::middleware('web')->group(function () use ($pages) {

            foreach ($pages as $page) {
                $controller = trim(str_replace('\\\\', '\\', $page->controller_name));

                // Skip invalid controller
                if (!class_exists($controller)) {
                    continue;
                }

                $httpMethod = strtoupper($page->http_method ?? 'GET');
                $methodName = $page->method_name ?: 'index';
                $urlPath = ltrim(trim($page->url_path), '/');
                $routeName = trim($page->route_name);

                // Helper to register one route
                $register = function (
                    string $path,
                    string $name,
                    array $middleware
                ) use ($controller, $methodName, $httpMethod) {

                    // Skip if route name already exists
                    if (Route::has($name)) {
                        return;
                    }

                    match ($httpMethod) {
                        'POST' => Route::post($path, [$controller, $methodName])
                            ->name($name)
                            ->middleware($middleware),

                        'PUT' => Route::put($path, [$controller, $methodName])
                            ->name($name)
                            ->middleware($middleware),

                        'PATCH' => Route::patch($path, [$controller, $methodName])
                            ->name($name)
                            ->middleware($middleware),

                        'DELETE' => Route::delete($path, [$controller, $methodName])
                            ->name($name)
                            ->middleware($middleware),

                        default => Route::get($path, [$controller, $methodName])
                            ->name($name)
                            ->middleware($middleware),
                    };
                };

                // 1) Register original route
                $originalMiddleware = ['auth'];
                if (str_starts_with($urlPath, 'admin')) {
                    $originalMiddleware[] = 'role:Admin';
                } elseif (str_starts_with($urlPath, 'manager')) {
                    $originalMiddleware[] = 'role:Manager';
                }

                $register($urlPath, $routeName, $originalMiddleware);

                // 2) If this is an admin page, also create Manager alias
                //    admin/test-dashboard  -> manager/test-dashboard
                //    admin.test-dashboard  -> manager.test-dashboard
                if (str_starts_with($urlPath, 'admin/') || $urlPath === 'admin') {
                    $managerPath = preg_replace('/^admin/', 'manager', $urlPath, 1);

                    $managerRouteName = $routeName;
                    if (str_starts_with($routeName, 'admin.')) {
                        $managerRouteName = preg_replace('/^admin\./', 'manager.', $routeName, 1);
                    } else {
                        // fallback if route_name doesn't start with admin.
                        $managerRouteName = 'manager.' . ltrim($routeName, '.');
                    }

                    $register($managerPath, $managerRouteName, ['auth', 'role:Manager']);
                }
            }
        });
    }
} catch (\Throwable $e) {
    Log::warning('Dynamic routes failed: ' . $e->getMessage());
}
