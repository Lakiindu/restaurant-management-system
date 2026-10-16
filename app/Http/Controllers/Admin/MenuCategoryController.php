<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class MenuCategoryController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $panel = strtolower($user->role->role_name ?? 'admin');
        return view('admin.menu-categories.index', compact('panel'));
    }

    public function fetch(Request $request)
    {
        try {
            $query = MenuCategory::query();

            // Only count items if menu_items table exists
            if (Schema::hasTable('menu_items')) {
                $query->withCount('menuItems');
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $rows = $query->orderBy('sort_order')->orderBy('id', 'desc')->paginate(10);

            $data = $rows->map(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'color' => $row->color,
                    'icon' => $row->icon ?? 'bi-cup-hot',
                    'description' => $row->description ?? '-',
                    'items_count' => $row->menu_items_count ?? 0,
                    'sort_order' => $row->sort_order,
                    'status' => (int) $row->status,
                    'created_at' => Carbon::parse($row->created_at)->format('M d, Y'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $rows->currentPage(),
                    'last_page' => $rows->lastPage(),
                    'total' => $rows->total(),
                    'from' => $rows->firstItem(),
                    'to' => $rows->lastItem(),
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error loading menu categories.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function get(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasOptionPermission('MENU_CAT_VIEW')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $row = MenuCategory::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $row]);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasOptionPermission('MENU_CAT_ADD')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:menu_categories,name',
            'color' => 'required|string|max:20',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        MenuCategory::create($request->all());

        return response()->json(['success' => true, 'message' => 'Menu category created successfully!']);
    }

    public function update(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasOptionPermission('MENU_CAT_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $row = MenuCategory::find($id);
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100', Rule::unique('menu_categories', 'name')->ignore($row->id)],
            'color' => 'required|string|max:20',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $row->update($request->all());

        return response()->json(['success' => true, 'message' => 'Menu category updated successfully!']);
    }

    public function destroy(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasOptionPermission('MENU_CAT_DELETE')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $row = MenuCategory::find($id);
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        // Protect if menu items exist in this category
        if (Schema::hasTable('menu_items')) {
            $count = $row->menuItems()->count();
            if ($count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete. {$count} menu item(s) are using this category."
                ], 403);
            }
        }

        $row->delete();
        return response()->json(['success' => true, 'message' => 'Menu category deleted successfully!']);
    }

    public function toggleStatus(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasOptionPermission('MENU_CAT_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $row = MenuCategory::find($id);
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $row->status = $row->status == 1 ? 0 : 1;
        $row->save();

        return response()->json(['success' => true, 'message' => 'Status updated!']);
    }

    public function active()
    {
        $rows = MenuCategory::where('status', 1)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'color', 'icon']);

        return response()->json(['success' => true, 'data' => $rows]);
    }
}
