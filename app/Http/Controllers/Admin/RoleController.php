<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;  
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    // ============================================
    // Display Roles Page
    // ============================================
    public function index()
    {
        return view('admin.roles.index');
    }

    // ============================================
    // AJAX: Fetch Roles
    // ============================================
    public function fetchRoles(Request $request)
    {
        $query = Role::withCount('users');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('role_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $roles = $query->orderBy('created_at', 'desc')->paginate(10);

        $data = $roles->map(function ($role) {
            return [
                'role_id' => $role->role_id,
                'role_name' => $role->role_name,
                'description' => $role->description ?? '-',
                'users_count' => $role->users_count,
                'status' => $role->status,
                'is_admin' => $role->role_id == 1, // Protect Admin role
                'created_at' => \Carbon\Carbon::parse($role->created_at)->format('M d, Y'),
                'updated_at' => \Carbon\Carbon::parse($role->updated_at)->format('M d, Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
                'from' => $roles->firstItem(),
                'to' => $roles->lastItem(),
            ]
        ]);
    }

    // ============================================
    // AJAX: Get Single Role
    // ============================================
    public function getRole(int $id)
    {
        $role = Role::withCount('users')->find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'role_id' => $role->role_id,
                'role_name' => $role->role_name,
                'description' => $role->description,
                'status' => $role->status,
                'users_count' => $role->users_count,
                'is_admin' => $role->role_id == 1,
                'created_at' => \Carbon\Carbon::parse($role->created_at)->format('M d, Y h:i A'),
                'updated_at' => \Carbon\Carbon::parse($role->updated_at)->format('M d, Y h:i A'),
            ]
        ]);
    }

    // ============================================
    // AJAX: Store New Role
    // ============================================
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_name' => 'required|string|max:45|unique:roles,role_name',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ], [
            'role_name.required' => 'Role name is required.',
            'role_name.unique' => 'This role name already exists.',
            'role_name.max' => 'Role name cannot exceed 45 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Role::create([
            'role_name' => $request->role_name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully!'
        ]);
    }

    // ============================================
    // AJAX: Update Role
    // ============================================
    public function update(Request $request, int $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        // Protect Admin role name from being changed
        if ($role->role_id == 1 && $request->role_name !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admin role name cannot be changed!'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'role_name' => ['required', 'string', 'max:45', Rule::unique('roles', 'role_name')->ignore($role->role_id, 'role_id')],
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent Admin role from being deactivated
        if ($role->role_id == 1 && $request->status == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Admin role cannot be deactivated!'
            ], 403);
        }

        $role->update([
            'role_name' => $request->role_name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully!'
        ]);
    }

    // ============================================
    // AJAX: Delete Role
    // ============================================
    public function destroy(int $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        // Protect Admin role
        if ($role->role_id == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Admin role cannot be deleted!'
            ], 403);
        }

        // Check if users are assigned to this role
        $usersCount = User::where('role_id', $id)->count();
        if ($usersCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete this role. {$usersCount} user(s) are assigned to it. Please reassign them first."
            ], 403);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully!'
        ]);
    }

    // ============================================
    // AJAX: Toggle Status
    // ============================================
    public function toggleStatus(int $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        // Protect Admin role
        if ($role->role_id == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Admin role status cannot be changed!'
            ], 403);
        }

        $role->status = $role->status == 1 ? 0 : 1;
        $role->save();

        $statusText = $role->status == 1 ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Role {$statusText} successfully!"
        ]);
    }

    // ============================================
    // AJAX: Get All Active Roles (for dropdowns)
    // ============================================
    public function getActiveRoles()
    {
        $roles = Role::where('status', 1)
            ->orderBy('role_name')
            ->get(['role_id', 'role_name']);

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }
}