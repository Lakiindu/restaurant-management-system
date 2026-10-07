<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ItemCategoryController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $panel = $user->role->role_name === 'Manager' ? 'manager' : 'admin';
        return view('admin.item-categories.index', compact('panel'));
    }

    public function fetch(Request $request)
    {
        try {
            $query = ItemCategory::query();

            // Count sub categories only if table exists
            if (Schema::hasTable('item_sub_categories')) {
                $query->withCount('subCategories');
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

            $rows = $query->orderBy('id', 'desc')->paginate(10);

            $data = $rows->map(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'description' => $row->description ?? '-',
                    'sub_categories_count' => $row->sub_categories_count ?? 0,
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
                    'per_page' => $rows->perPage(),
                    'total' => $rows->total(),
                    'from' => $rows->firstItem(),
                    'to' => $rows->lastItem(),
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('ItemCategory fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error while loading categories.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function get(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_CATEGORY_VIEW')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $row = ItemCategory::query();
        if (Schema::hasTable('item_sub_categories')) {
            $row = $row->withCount('subCategories');
        }
        $row = $row->find($id);

        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $row->id,
                'name' => $row->name,
                'description' => $row->description,
                'sub_categories_count' => $row->sub_categories_count ?? 0,
                'status' => (int) $row->status,
                'created_at' => Carbon::parse($row->created_at)->format('M d, Y h:i A'),
                'updated_at' => Carbon::parse($row->updated_at)->format('M d, Y h:i A'),
            ]
        ]);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_CATEGORY_ADD')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot add categories.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:item_categories,name',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        ItemCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Item category created successfully!']);
    }

    public function update(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_CATEGORY_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot edit categories.'], 403);
        }

        $row = ItemCategory::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100', Rule::unique('item_categories', 'name')->ignore($row->id)],
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $row->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Item category updated successfully!']);
    }

    public function destroy(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_CATEGORY_DELETE')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot delete categories.'], 403);
        }

        $row = ItemCategory::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        // Protect if sub categories exist
        if (Schema::hasTable('item_sub_categories')) {
            $count = $row->subCategories()->count();
            if ($count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete. {$count} sub categor" . ($count > 1 ? 'ies are' : 'y is') . " linked."
                ], 403);
            }
        }

        $row->delete();
        return response()->json(['success' => true, 'message' => 'Item category deleted successfully!']);
    }

    public function toggleStatus(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_CATEGORY_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot edit categories.'], 403);
        }

        $row = ItemCategory::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        $row->status = $row->status == 1 ? 0 : 1;
        $row->save();

        return response()->json(['success' => true, 'message' => 'Item category status updated!']);
    }

    public function active()
    {
        $rows = ItemCategory::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['success' => true, 'data' => $rows]);
    }
}
