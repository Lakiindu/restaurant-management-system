<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Admin role ID constant
    const ADMIN_ROLE_ID = 1;

    // ============================================
    // Display User List Page
    // ============================================
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user && $user->role && $user->role->role_name === 'Manager') {
            return view('manager.users.index');
        }

        $roles = Role::where('status', 1)->get();
        return view('admin.users.index', compact('roles'));
    }

    // ============================================
    // AJAX: Fetch Users
    // ============================================
    public function fetchUsers(Request $request)
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        $query = User::with('role');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }

        // Filter by Status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Get paginated users
        $users = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Format data
        $data = $users->map(function (User $user) use ($authUser) {
            return [
                'user_id' => $user->user_id,
                'user_name' => $user->user_name,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role_name' => $user->role?->role_name ?? 'N/A',
                'status' => $user->status,
                'is_self' => $authUser && $user->user_id === $authUser->user_id,
                'is_admin' => $user->role_id == self::ADMIN_ROLE_ID,
                'initial' => strtoupper(substr($user->user_name, 0, 1)),
                'created_at' => Carbon::parse($user->created_at)->format('M d, Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ]);
    }

    // ============================================
    // AJAX: Get Single User
    // ============================================
    public function getUser(int $id)
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        $user = User::with('role')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->user_id,
                'user_name' => $user->user_name,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role_name' => $user->role?->role_name ?? 'N/A',
                'status' => $user->status,
                'must_change_password' => $user->must_change_password,
                'is_self' => $authUser && $user->user_id === $authUser->user_id,
                'is_admin' => $user->role_id == self::ADMIN_ROLE_ID,
                'created_at' => Carbon::parse($user->created_at)->format('M d, Y h:i A'),
                'updated_at' => Carbon::parse($user->updated_at)->format('M d, Y h:i A'),
                'initial' => strtoupper(substr($user->user_name, 0, 1)),
            ],
        ]);
    }

    // ============================================
    // AJAX: Store New User
    // ============================================
    public function store(Request $request)
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        // 🔒 Backend Permission Check
        if ($authUser && !$authUser->hasOptionPermission('USER_ADD')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to add users.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_name' => 'required|string|max:45|unique:users,user_name',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,role_id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        User::create([
            'user_name' => $request->input('user_name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role_id' => $request->input('role_id'),
            'status' => $request->input('status'),
            'must_change_password' => $request->boolean('must_change_password'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully!',
        ]);
    }

    // ============================================
    // AJAX: Update User
    // ============================================
    public function update(Request $request, int $id)
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        // 🔒 Backend Permission Check
        if ($authUser && !$authUser->hasOptionPermission('USER_EDIT')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to edit users.'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $isSelf = $authUser && $user->user_id === $authUser->user_id;
        $isAdmin = $user->role_id == self::ADMIN_ROLE_ID;

        $validator = Validator::make($request->all(), [
            'user_name' => [
                'required',
                'string',
                'max:45',
                Rule::unique('users', 'user_name')
                    ->ignore($user->user_id, 'user_id'),
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('users', 'email')
                    ->ignore($user->user_id, 'user_id'),
            ],
            'password' => [
                'nullable',
                'string',
                'min:6',
                'confirmed',
            ],
            'role_id' => [
                'required',
                'exists:roles,role_id',
            ],
            'status' => [
                'required',
                'in:0,1',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 🛡️ PROTECTION 1: Cannot change your own role
        if ($isSelf && $request->input('role_id') != $user->role_id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot change your own role!',
            ], 403);
        }

        // 🛡️ PROTECTION 2: Cannot deactivate yourself
        if ($isSelf && $request->input('status') == 0) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own account!',
            ], 403);
        }

        // 🛡️ PROTECTION 3: Cannot deactivate Admin user
        if ($isAdmin && $request->input('status') == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Admin users cannot be deactivated!',
            ], 403);
        }

        // 🛡️ PROTECTION 4: Cannot change Admin role if last active admin
        if ($isAdmin && $request->input('role_id') != self::ADMIN_ROLE_ID) {
            $activeAdmins = User::where('role_id', self::ADMIN_ROLE_ID)
                ->where('status', 1)
                ->count();

            if ($activeAdmins <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot change role of the last active Admin!',
                ], 403);
            }
        }

        $data = [
            'user_name' => $request->input('user_name'),
            'email' => $request->input('email'),
            'role_id' => $request->input('role_id'),
            'status' => $request->input('status'),
            'must_change_password' => $request->boolean('must_change_password'),
        ];

        // Update password only if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully!',
        ]);
    }

    // ============================================
    // AJAX: Delete User
    // ============================================
    public function destroy(int $id)
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        // 🔒 Backend Permission Check
        if ($authUser && !$authUser->hasOptionPermission('USER_DELETE')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to delete users.'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Prevent deleting currently logged-in user
        if ($authUser && $user->user_id === $authUser->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account!',
            ], 403);
        }

        if ($user->role_id == self::ADMIN_ROLE_ID) {
            $activeAdmins = User::where('role_id', self::ADMIN_ROLE_ID)
                ->where('status', 1)
                ->count();

            if ($activeAdmins <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the last active Admin!',
                ], 403);
            }
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully!',
        ]);
    }

    // ============================================
    // AJAX: Toggle User Status
    // ============================================
    public function toggleStatus(int $id)
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        // 🔒 Backend Permission Check
        if ($authUser && !$authUser->hasOptionPermission('USER_EDIT')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to change user status.'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Prevent deactivating currently logged-in user
        if ($authUser && $user->user_id === $authUser->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own account!',
            ], 403);
        }

        if ($user->role_id == self::ADMIN_ROLE_ID && $user->status == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Admin users cannot be deactivated!',
            ], 403);
        }

        // Toggle status
        $user->status = $user->status == 1 ? 0 : 1;
        $user->save();

        $statusText = $user->status == 1 ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "User {$statusText} successfully!",
            'new_status' => $user->status,
        ]);
    }
}
