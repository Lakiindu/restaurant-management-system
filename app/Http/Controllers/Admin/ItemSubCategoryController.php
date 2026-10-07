<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use App\Models\ItemSubCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ItemSubCategoryController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $panel = $user->role->role_name === 'Manager' ? 'manager' : 'admin';
        return view('admin.item-sub-categories.index', compact('panel'));
    }

    public function fetch(Request $request)
    {
        try {
            $query = ItemSubCategory::with('category');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('item_category_id')) {
                $query->where('item_category_id', $request->item_category_id);
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $rows = $query->orderBy('id', 'desc')->paginate(10);

            $data = $rows->map(function ($row) {
                return [
                    'id' => $row->id,
                    'item_category_id' => $row->item_category_id,
                    'category_name' => $row->category->name ?? 'N/A',
                    'name' => $row->name,
                    'description' => $row->description ?? '-',
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
            Log::error('ItemSubCategory fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error while loading sub categories.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function get(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_SUB_CAT_VIEW')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $row = ItemSubCategory::with('category')->find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Sub category not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $row->id,
                'item_category_id' => $row->item_category_id,
                'category_name' => $row->category->name ?? 'N/A',
                'name' => $row->name,
                'description' => $row->description,
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

        if (!$user->hasOptionPermission('ITEM_SUB_CAT_ADD')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot add sub categories.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'item_category_id' => 'required|exists:item_categories,id',
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('item_sub_categories', 'name')->where(fn($q) => $q->where('item_category_id', $request->item_category_id))
            ],
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        ItemSubCategory::create([
            'item_category_id' => $request->item_category_id,
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Item sub category created successfully!']);
    }

    public function update(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_SUB_CAT_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot edit sub categories.'], 403);
        }

        $row = ItemSubCategory::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Sub category not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'item_category_id' => 'required|exists:item_categories,id',
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('item_sub_categories', 'name')
                    ->where(fn($q) => $q->where('item_category_id', $request->item_category_id))
                    ->ignore($row->id)
            ],
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $row->update([
            'item_category_id' => $request->item_category_id,
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Item sub category updated successfully!']);
    }

    public function destroy(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_SUB_CAT_DELETE')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot delete sub categories.'], 403);
        }

        $row = ItemSubCategory::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Sub category not found'], 404);
        }

        // Future Protection: Check if assigned to Items before deleting
        // (Will implement when Items table is built)

        $row->delete();

        return response()->json(['success' => true, 'message' => 'Item sub category deleted successfully!']);
    }

    public function toggleStatus(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_SUB_CAT_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot edit sub categories.'], 403);
        }

        $row = ItemSubCategory::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Sub category not found'], 404);
        }

        $row->status = $row->status == 1 ? 0 : 1;
        $row->save();

        return response()->json(['success' => true, 'message' => 'Item sub category status updated!']);
    }

    public function active(Request $request)
    {
        $query = ItemSubCategory::where('status', 1);

        if ($request->filled('item_category_id')) {
            $query->where('item_category_id', $request->item_category_id);
        }

        $rows = $query->orderBy('name')->get(['id', 'item_category_id', 'name']);

        return response()->json(['success' => true, 'data' => $rows]);
    }
}
