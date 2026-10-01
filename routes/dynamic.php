<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Dynamic Routes Loader
|--------------------------------------------------------------------------
| - Loads page routes dynamically from "pages" table
| - Wraps inside "web" middleware group (sessions/cookies/CSRF)
| - Automatically creates Manager route aliases for Admin pages
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
                $methodName = $page->method_name ?: 'index';

                // Skip invalid controller class or method
                if (!class_exists($controller) || !method_exists($controller, $methodName)) {
                    continue;
                }

                $httpMethod = strtoupper($page->http_method ?? 'GET');
                $urlPath    = ltrim(trim($page->url_path), '/');
                $routeName  = trim($page->route_name);

                // Helper closure to register one route
                $register = function (string $path, string $name, array $middleware) use ($controller, $methodName, $httpMethod) {

                    // Skip if route name already registered in web.php (prevents conflicts)
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

                // 1) Register Primary Route
                $originalMiddleware = ['auth'];
                if (str_starts_with($urlPath, 'admin')) {
                    $originalMiddleware[] = 'role:Admin';
                } elseif (str_starts_with($urlPath, 'manager')) {
                    $originalMiddleware[] = 'role:Manager';
                }

                $register($urlPath, $routeName, $originalMiddleware);

                // 2) If Admin page, automatically register Manager Alias
                if (str_starts_with($urlPath, 'admin/') || $urlPath === 'admin') {
                    $managerPath = preg_replace('/^admin/', 'manager', $urlPath, 1);

                    $managerRouteName = $routeName;
                    if (str_starts_with($routeName, 'admin.')) {
                        $managerRouteName = preg_replace('/^admin\./', 'manager.', $routeName, 1);
                    } else {
                        $managerRouteName = 'manager.' . ltrim($routeName, '.');
                    }

                    $register($managerPath, $managerRouteName, ['auth', 'role:Manager']);
                }
            }
        });
    }
} catch (\Throwable $e) {
    Log::warning('Dynamic routes failed to load: ' . $e->getMessage());
}
