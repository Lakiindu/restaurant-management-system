<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MenuCategoryController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $panel = $user->role->role_name === 'Manager' ? 'manager' : 'admin';
        return view('admin.menu-categories.index', compact('panel'));
    }

    public function fetchCategories(Request $request)
    {
        $query = MenuCategory::withCount('items');

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
                'items_count' => $cat->items_count,
                'status' => $cat->status,
                'created_at' => \Carbon\Carbon::parse($cat->created_at)->format('M d, Y'),
                'updated_at' => \Carbon\Carbon::parse($cat->updated_at)->format('M d, Y'),
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

    public function getCategory(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('MENU_CAT_VIEW')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $category = MenuCategory::withCount('items')->find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'category_id' => $category->category_id,
                'category_name' => $category->category_name,
                'description' => $category->description,
                'status' => $category->status,
                'items_count' => $category->items_count,
                'created_at' => \Carbon\Carbon::parse($category->created_at)->format('M d, Y h:i A'),
                'updated_at' => \Carbon\Carbon::parse($category->updated_at)->format('M d, Y h:i A'),
            ]
        ]);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('MENU_CAT_ADD')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - You cannot add menu categories.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:100|unique:menu_categories,category_name',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        MenuCategory::create([
            'category_name' => $request->category_name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Menu category created successfully!']);
    }

    public function update(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('MENU_CAT_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - You cannot edit menu categories.'], 403);
        }

        $category = MenuCategory::find($id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_name' => ['required', 'string', 'max:100', Rule::unique('menu_categories', 'category_name')->ignore($category->category_id, 'category_id')],
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $category->update([
            'category_name' => $request->category_name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Menu category updated successfully!']);
    }

    public function destroy(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('MENU_CAT_DELETE')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - You cannot delete menu categories.'], 403);
        }

        $category = MenuCategory::find($id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        $itemsCount = $category->items()->count();
        if ($itemsCount > 0) {
            return response()->json(['success' => false, 'message' => "Cannot delete. {$itemsCount} menu item(s) are using this category."], 403);
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => 'Menu category deleted successfully!']);
    }

    public function toggleStatus(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('MENU_CAT_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - You cannot edit menu categories.'], 403);
        }

        $category = MenuCategory::find($id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        $category->status = $category->status == 1 ? 0 : 1;
        $category->save();

        return response()->json(['success' => true, 'message' => "Category status updated!"]);
    }

    public function getActiveCategories()
    {
        $categories = MenuCategory::where('status', 1)->orderBy('category_name')->get(['category_id', 'category_name']);
        return response()->json(['success' => true, 'data' => $categories]);
    }
}
