<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\RoleOption;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoleOptionController extends Controller
{
    // ============================================
    // Display Role Options Page
    // ============================================
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $panel = ($user && $user->role && $user->role->role_name === 'Manager') ? 'manager' : 'admin';

        return view('admin.role-options.index', compact('panel'));
    }

    // ============================================
    // AJAX: Fetch Role Options
    // ============================================
    public function fetchOptions(Request $request)
    {
        $query = RoleOption::with('page.category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('option_name', 'like', "%{$search}%")
                    ->orWhere('option_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('page_id')) {
            $query->where('page_id', $request->page_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $options = $query->orderBy('id', 'desc')->paginate(10);

        $data = $options->map(function ($opt) {
            return [
                'id' => $opt->id,
                'option_name' => $opt->option_name,
                'option_code' => $opt->option_code,
                'page_id' => $opt->page_id,
                'page_name' => $opt->page->page_name ?? 'N/A',
                'page_code' => $opt->page->page_code ?? ($opt->page->code ?? 'N/A'), // 👈 Added page_code here!
                'category_name' => $opt->page->category->category_name ?? 'N/A',
                'status' => $opt->status,
                'created_at' => \Carbon\Carbon::parse($opt->created_at)->format('M d, Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $options->currentPage(),
                'last_page' => $options->lastPage(),
                'per_page' => $options->perPage(),
                'total' => $options->total(),
                'from' => $options->firstItem(),
                'to' => $options->lastItem(),
            ]
        ]);
    }

    // ============================================
    // AJAX: Get Single Option
    // ============================================
    public function getOption(int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $option = RoleOption::with('page')->find($id);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Option not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $option->id,
                'option_name' => $option->option_name,
                'option_code' => $option->option_code,
                'page_id' => $option->page_id,
                'page_name' => $option->page?->page_name ?? 'N/A',
                'status' => $option->status,
            ]
        ]);
    }

    // ============================================
    // AJAX: Store New Option
    // ============================================
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // 🔒 Backend Permission Check
        if ($user && !$user->hasOptionPermission('OPTION_ADD')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to add options.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'option_name' => 'required|string|max:45',
            'option_code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Z0-9_]+$/',
                'unique:role_options,option_code'
            ],
            'page_id' => 'required|exists:pages,page_id',
            'status' => 'required|in:0,1',
        ], [
            'option_code.regex' => 'Option code must be UPPERCASE letters, numbers, and underscores only.',
            'option_code.unique' => 'This option code already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        RoleOption::create([
            'option_name' => $request->option_name,
            'option_code' => strtoupper($request->option_code),
            'page_id' => $request->page_id,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Option created successfully!'
        ]);
    }

    // ============================================
    // AJAX: Update Option
    // ============================================
    public function update(Request $request, int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        // 🔒 Backend Permission Check
        if ($user && !$user->hasOptionPermission('OPTION_EDIT')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to edit options.'
            ], 403);
        }

        $option = RoleOption::find($id);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Option not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'option_name' => 'required|string|max:45',
            'option_code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Z0-9_]+$/',
                Rule::unique('role_options', 'option_code')->ignore($option->id)
            ],
            'page_id' => 'required|exists:pages,page_id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $option->update([
            'option_name' => $request->option_name,
            'option_code' => strtoupper($request->option_code),
            'page_id' => $request->page_id,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Option updated successfully!'
        ]);
    }

    // ============================================
    // AJAX: Delete Option
    // ============================================
    public function destroy(int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        // 🔒 Backend Permission Check
        if ($user && !$user->hasOptionPermission('OPTION_DELETE')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to delete options.'
            ], 403);
        }

        $option = RoleOption::find($id);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Option not found'
            ], 404);
        }

        $option->delete();

        return response()->json([
            'success' => true,
            'message' => 'Option deleted successfully!'
        ]);
    }

    // ============================================
    // AJAX: Toggle Status
    // ============================================
    public function toggleStatus(int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        // 🔒 Backend Permission Check
        if ($user && !$user->hasOptionPermission('OPTION_EDIT')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to change option status.'
            ], 403);
        }

        $option = RoleOption::find($id);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Option not found'
            ], 404);
        }

        $option->status = $option->status == 1 ? 0 : 1;
        $option->save();

        $statusText = $option->status == 1 ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Option {$statusText} successfully!"
        ]);
    }
}
