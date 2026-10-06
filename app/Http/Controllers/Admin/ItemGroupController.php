<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ItemGroupController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $panel = $user->role->role_name === 'Manager' ? 'manager' : 'admin';
        return view('admin.item-groups.index', compact('panel'));
    }

    public function fetch(Request $request)
    {
        try {
            $query = ItemGroup::query();

            // Only count sub groups if table exists
            if (Schema::hasTable('item_sub_groups')) {
                $query->withCount('subGroups');
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
                    'sub_groups_count' => $row->sub_groups_count ?? 0, // safe default
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
            Log::error('ItemGroup fetch failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Server error while loading item groups.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function get(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_GROUP_VIEW')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $row = ItemGroup::withCount('subGroups')->find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Item group not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $row->id,
                'name' => $row->name,
                'description' => $row->description,
                'sub_groups_count' => $row->sub_groups_count,
                'status' => $row->status,
                'created_at' => Carbon::parse($row->created_at)->format('M d, Y h:i A'),
                'updated_at' => Carbon::parse($row->updated_at)->format('M d, Y h:i A'),
            ]
        ]);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_GROUP_ADD')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot add item groups.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:item_groups,name',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        ItemGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Item group created successfully!']);
    }

    public function update(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_GROUP_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot edit item groups.'], 403);
        }

        $row = ItemGroup::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Item group not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100', Rule::unique('item_groups', 'name')->ignore($row->id)],
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

        return response()->json(['success' => true, 'message' => 'Item group updated successfully!']);
    }

    public function destroy(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_GROUP_DELETE')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot delete item groups.'], 403);
        }

        $row = ItemGroup::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Item group not found'], 404);
        }

        $count = $row->subGroups()->count();
        if ($count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete. {$count} sub group(s) are linked to this group."
            ], 403);
        }

        $row->delete();
        return response()->json(['success' => true, 'message' => 'Item group deleted successfully!']);
    }

    public function toggleStatus(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_GROUP_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot edit item groups.'], 403);
        }

        $row = ItemGroup::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Item group not found'], 404);
        }

        $row->status = $row->status == 1 ? 0 : 1;
        $row->save();

        return response()->json(['success' => true, 'message' => 'Item group status updated!']);
    }

    public function active()
    {
        $rows = ItemGroup::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['success' => true, 'data' => $rows]);
    }
}
