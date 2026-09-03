<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PageCategoryController extends Controller
{
    // ============================================
    // Display Categories Page
    // ============================================
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $panel = ($user && $user->role && $user->role->role_name === 'Manager') ? 'manager' : 'admin';

        return view('admin.page-categories.index', compact('panel'));
    }

    // ============================================
    // AJAX: Fetch Categories
    // ============================================
    public function fetchCategories(Request $request)
    {
        $query = PageCategory::withCount('pages');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('category_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $categories = $query->orderBy('created_at', 'desc')->paginate(10);

        $data = $categories->map(function ($cat) {
            return [
                'category_id' => $cat->category_id,
                'category_name' => $cat->category_name,
                'description' => $cat->description ?? '-',
                'pages_count' => $cat->pages_count,
                'status' => $cat->status,
                'created_at' => Carbon::parse($cat->created_at)->format('M d, Y'),
                'updated_at' => Carbon::parse($cat->updated_at)->format('M d, Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
                'from' => $categories->firstItem(),
                'to' => $categories->lastItem(),
            ]
        ]);
    }

    // ============================================
    // AJAX: Get Single Category
    // ============================================
    public function getCategory(int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $category = PageCategory::withCount('pages')->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'category_id' => $category->category_id,
                'category_name' => $category->category_name,
                'description' => $category->description,
                'status' => $category->status,
                'pages_count' => $category->pages_count,
                'created_at' => Carbon::parse($category->created_at)->format('M d, Y h:i A'),
                'updated_at' => Carbon::parse($category->updated_at)->format('M d, Y h:i A'),
            ]
        ]);
    }

    // ============================================
    // AJAX: Store New Category
    // ============================================
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // 🔒 Backend Permission Check
        if ($user && !$user->hasOptionPermission('CATEGORY_ADD')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to add categories.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:45|unique:page_categories,category_name',
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

        PageCategory::create([
            'category_name' => $request->category_name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully!'
        ]);
    }

    // ============================================
    // AJAX: Update Category
    // ============================================
    public function update(Request $request, int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        // 🔒 Backend Permission Check
        if ($user && !$user->hasOptionPermission('CATEGORY_EDIT')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to edit categories.'
            ], 403);
        }

        $category = PageCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_name' => ['required', 'string', 'max:45', Rule::unique('page_categories', 'category_name')->ignore($category->category_id, 'category_id')],
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

        $category->update([
            'category_name' => $request->category_name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully!'
        ]);
    }

    // ============================================
    // AJAX: Delete Category
    // ============================================
    public function destroy(int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        // 🔒 Backend Permission Check
        if ($user && !$user->hasOptionPermission('CATEGORY_DELETE')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to delete categories.'
            ], 403);
        }

        $category = PageCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $pagesCount = $category->pages()->count();
        if ($pagesCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete this category. {$pagesCount} page(s) are using it. Please delete or move them first."
            ], 403);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully!'
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
        if ($user && !$user->hasOptionPermission('CATEGORY_EDIT')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You do not have permission to change category status.'
            ], 403);
        }

        $category = PageCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->status = $category->status == 1 ? 0 : 1;
        $category->save();

        $statusText = $category->status == 1 ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Category {$statusText} successfully!"
        ]);
    }

    // ============================================
    // AJAX: Get Active Categories (for dropdowns)
    // ============================================
    public function getActiveCategories()
    {
        $categories = PageCategory::where('status', 1)
            ->orderBy('category_name')
            ->get(['category_id', 'category_name']);

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}
