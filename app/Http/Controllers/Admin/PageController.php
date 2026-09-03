<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    // ============================================
    // Display Pages
    // ============================================
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $panel = ($user && $user->role && $user->role->role_name === 'Manager') ? 'manager' : 'admin';

        return view('admin.pages.index', compact('panel'));
    }

    // ============================================
    // AJAX: Fetch Pages
    // ============================================
    public function fetchPages(Request $request)
    {
        $query = Page::with('category')->withCount('roleOptions');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('page_name', 'like', "%{$search}%")
                    ->orWhere('page_code', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $pages = $query->orderBy('created_at', 'desc')->paginate(10);

        $data = $pages->map(function ($page) {
            return [
                'page_id' => $page->page_id,
                'page_name' => $page->page_name,
                'page_code' => $page->page_code,
                'route_name' => $page->route_name ?? '-',
                'description' => $page->description ?? '-',
                'category_name' => $page->category?->category_name ?? 'N/A',
                'category_id' => $page->category_id,
                'options_count' => $page->role_options_count ?? 0,
                'status' => $page->status,
                'created_at' => Carbon::parse($page->created_at)->format('M d, Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
                'from' => $pages->firstItem(),
                'to' => $pages->lastItem(),
            ]
        ]);
    }

    // ============================================
    // AJAX: Get Single Page
    // ============================================
    public function getPage(int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $page = Page::with('category')->find($id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'page_id' => $page->page_id,
                'page_name' => $page->page_name,
                'page_code' => $page->page_code,
                'route_name' => $page->route_name,
                'description' => $page->description,
                'category_id' => $page->category_id,
                'category_name' => $page->category?->category_name ?? 'N/A',
                'status' => $page->status,
                'created_at' => Carbon::parse($page->created_at)->format('M d, Y h:i A'),
                'updated_at' => Carbon::parse($page->updated_at)->format('M d, Y h:i A'),
            ]
        ]);
    }

    // ============================================
    // AJAX: Store New Page
    // ============================================
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // 🔒 Backend Permission Check
        if ($user && !$user->hasOptionPermission('PAGE_ADD')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to add pages.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'page_name' => 'required|string|max:45',
            'page_code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Z0-9_]+$/',
                'unique:pages,page_code'
            ],
            'route_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'category_id' => 'required|exists:page_categories,category_id',
            'status' => 'required|in:0,1',
        ], [
            'page_code.regex' => 'Page code must be UPPERCASE letters, numbers, and underscores only.',
            'page_code.unique' => 'This page code already exists.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Page::create([
            'page_name' => $request->page_name,
            'page_code' => strtoupper($request->page_code),
            'route_name' => $request->route_name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page created successfully!'
        ]);
    }

    // ============================================
    // AJAX: Update Page
    // ============================================
    public function update(Request $request, int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        // 🔒 Backend Permission Check
        if ($user && !$user->hasOptionPermission('PAGE_EDIT')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to edit pages.'
            ], 403);
        }

        $page = Page::find($id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'page_name' => 'required|string|max:45',
            'page_code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Z0-9_]+$/',
                Rule::unique('pages', 'page_code')
                    ->ignore($page->page_id, 'page_id')
            ],
            'route_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'category_id' => 'required|exists:page_categories,category_id',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $page->update([
            'page_name' => $request->page_name,
            'page_code' => strtoupper($request->page_code),
            'route_name' => $request->route_name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully!'
        ]);
    }

    // ============================================
    // AJAX: Delete Page
    // ============================================
    public function destroy(int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        // 🔒 Backend Permission Check
        if ($user && !$user->hasOptionPermission('PAGE_DELETE')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to delete pages.'
            ], 403);
        }

        $page = Page::find($id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found'
            ], 404);
        }

        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully!'
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
        if ($user && !$user->hasOptionPermission('PAGE_EDIT')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to change page status.'
            ], 403);
        }

        $page = Page::find($id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found'
            ], 404);
        }

        $page->status = $page->status == 1 ? 0 : 1;
        $page->save();

        $statusText = $page->status == 1 ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Page {$statusText} successfully!"
        ]);
    }

    // ============================================
    // AJAX: Get Active Pages (for dropdowns)
    // ============================================
    public function getActivePages()
    {
        $pages = Page::where('status', 1)
            ->with('category')
            ->orderBy('page_name')
            ->get(['page_id', 'page_name', 'page_code', 'category_id']);

        return response()->json([
            'success' => true,
            'data' => $pages
        ]);
    }
}
