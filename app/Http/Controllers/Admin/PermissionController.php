<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\RoleOption;
use App\Models\RolePermission;
use App\Models\RoleOptionPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    // ============================================
    // Display Permissions Page
    // ============================================
    public function index()
    {
        return view('admin.permissions.index');
    }

    // ============================================
    // AJAX: Get All Active Roles (except Admin)
    // ============================================
    public function getRoles()
    {
        $roles = Role::where('status', 1)
            ->orderBy('role_name')
            ->get(['role_id', 'role_name', 'description']);

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    // ============================================
    // AJAX: Get Pages Grouped by Category with Options & Permissions
    // ============================================
    public function getPermissions(int $roleId)
    {
        $role = Role::find($roleId);
        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        // Get all active categories with their pages and options
        $categories = PageCategory::where('status', 1)
            ->with(['pages' => function ($q) {
                $q->where('status', 1)->with(['roleOptions' => function ($q2) {
                    $q2->where('status', 1);
                }]);
            }])
            ->orderBy('category_name')
            ->get();

        // Get role's page permissions
        $pagePermissions = RolePermission::where('role_id', $roleId)
            ->where('allow', 1)
            ->pluck('page_code')
            ->toArray();

        // Get role's option permissions
        $optionPermissions = RoleOptionPermission::where('roles_id', $roleId)
            ->where('allow', 1)
            ->pluck('option_code')
            ->toArray();

        // Format data
        $data = $categories->map(function ($category) use ($pagePermissions, $optionPermissions) {
            return [
                'category_id' => $category->category_id,
                'category_name' => $category->category_name,
                'description' => $category->description,
                'pages' => $category->pages->map(function ($page) use ($pagePermissions, $optionPermissions) {
                    return [
                        'page_id' => $page->page_id,
                        'page_name' => $page->page_name,
                        'page_code' => $page->page_code,
                        'description' => $page->description,
                        'has_permission' => in_array($page->page_code, $pagePermissions),
                        'options' => $page->roleOptions->map(function ($opt) use ($optionPermissions) {
                            return [
                                'id' => $opt->id,
                                'option_name' => $opt->option_name,
                                'option_code' => $opt->option_code,
                                'has_permission' => in_array($opt->option_code, $optionPermissions),
                            ];
                        })
                    ];
                })
            ];
        });

        return response()->json([
            'success' => true,
            'role' => [
                'role_id' => $role->role_id,
                'role_name' => $role->role_name,
                'is_admin' => $role->role_id == 1,
            ],
            'data' => $data
        ]);
    }

    // ============================================
    // AJAX: Save Permissions
    // ============================================
    public function savePermissions(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,role_id',
            'page_codes' => 'nullable|array',
            'option_codes' => 'nullable|array',
        ]);

        $roleId = $request->role_id;

        // Prevent modifying Admin role permissions
        if ($roleId == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Admin role permissions cannot be modified!'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // ==================
            // Save PAGE permissions
            // ==================
            $pageCodes = $request->page_codes ?? [];

            // Delete all existing page permissions for this role
            RolePermission::where('role_id', $roleId)->delete();

            // Insert new page permissions
            foreach ($pageCodes as $pageCode) {
                RolePermission::create([
                    'role_id' => $roleId,
                    'page_code' => $pageCode,
                    'allow' => 1,
                ]);
            }

            // ==================
            // Save OPTION permissions
            // ==================
            $optionCodes = $request->option_codes ?? [];

            // Delete all existing option permissions for this role
            RoleOptionPermission::where('roles_id', $roleId)->delete();

            // Insert new option permissions
            foreach ($optionCodes as $optionCode) {
                RoleOptionPermission::create([
                    'roles_id' => $roleId,
                    'option_code' => $optionCode,
                    'allow' => 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permissions saved successfully!',
                'summary' => [
                    'pages_count' => count($pageCodes),
                    'options_count' => count($optionCodes),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // AJAX: Copy Permissions from Another Role
    // ============================================
    public function copyPermissions(Request $request)
    {
        $request->validate([
            'source_role_id' => 'required|exists:roles,role_id',
            'target_role_id' => 'required|exists:roles,role_id|different:source_role_id',
        ]);

        $targetRoleId = $request->target_role_id;

        if ($targetRoleId == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify Admin role permissions!'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Get source permissions
            $sourcePages = RolePermission::where('role_id', $request->source_role_id)
                ->where('allow', 1)
                ->pluck('page_code');

            $sourceOptions = RoleOptionPermission::where('roles_id', $request->source_role_id)
                ->where('allow', 1)
                ->pluck('option_code');

            // Delete target's existing permissions
            RolePermission::where('role_id', $targetRoleId)->delete();
            RoleOptionPermission::where('roles_id', $targetRoleId)->delete();

            // Copy pages
            foreach ($sourcePages as $pageCode) {
                RolePermission::create([
                    'role_id' => $targetRoleId,
                    'page_code' => $pageCode,
                    'allow' => 1,
                ]);
            }

            // Copy options
            foreach ($sourceOptions as $optionCode) {
                RoleOptionPermission::create([
                    'roles_id' => $targetRoleId,
                    'option_code' => $optionCode,
                    'allow' => 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permissions copied successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to copy permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // AJAX: Clear All Permissions for a Role
    // ============================================
    public function clearPermissions(int $roleId)
    {
        if ($roleId == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot clear Admin permissions!'
            ], 403);
        }

        $role = Role::find($roleId);
        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        DB::beginTransaction();
        try {
            RolePermission::where('role_id', $roleId)->delete();
            RoleOptionPermission::where('roles_id', $roleId)->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'All permissions cleared for ' . $role->role_name
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear permissions'
            ], 500);
        }
    }
}
