<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemGroup;
use App\Models\ItemSubGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ItemSubGroupController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $panel = $user->role->role_name === 'Manager' ? 'manager' : 'admin';
        return view('admin.item-sub-groups.index', compact('panel'));
    }

    public function fetch(Request $request)
    {
        try {
            $query = ItemSubGroup::with('group');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('item_group_id')) {
                $query->where('item_group_id', $request->item_group_id);
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $rows = $query->orderBy('id', 'desc')->paginate(10);

            $data = $rows->map(function ($row) {
                return [
                    'id' => $row->id,
                    'item_group_id' => $row->item_group_id,
                    'group_name' => $row->group->name ?? 'N/A',
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
            Log::error('ItemSubGroup fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error while loading sub groups.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function get(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_SUB_GROUP_VIEW')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $row = ItemSubGroup::with('group')->find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Sub group not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $row->id,
                'item_group_id' => $row->item_group_id,
                'group_name' => $row->group->name ?? 'N/A',
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

        if (!$user->hasOptionPermission('ITEM_SUB_GROUP_ADD')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot add sub groups.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'item_group_id' => 'required|exists:item_groups,id',
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('item_sub_groups', 'name')->where(fn($q) => $q->where('item_group_id', $request->item_group_id))
            ],
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        ItemSubGroup::create([
            'item_group_id' => $request->item_group_id,
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Item sub group created successfully!']);
    }

    public function update(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_SUB_GROUP_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot edit sub groups.'], 403);
        }

        $row = ItemSubGroup::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Sub group not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'item_group_id' => 'required|exists:item_groups,id',
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('item_sub_groups', 'name')
                    ->where(fn($q) => $q->where('item_group_id', $request->item_group_id))
                    ->ignore($row->id)
            ],
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $row->update([
            'item_group_id' => $request->item_group_id,
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Item sub group updated successfully!']);
    }

    public function destroy(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_SUB_GROUP_DELETE')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot delete sub groups.'], 403);
        }

        $row = ItemSubGroup::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Sub group not found'], 404);
        }

        // Later when items table exists, protect if linked
        $row->delete();

        return response()->json(['success' => true, 'message' => 'Item sub group deleted successfully!']);
    }

    public function toggleStatus(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_SUB_GROUP_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - cannot edit sub groups.'], 403);
        }

        $row = ItemSubGroup::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Sub group not found'], 404);
        }

        $row->status = $row->status == 1 ? 0 : 1;
        $row->save();

        return response()->json(['success' => true, 'message' => 'Item sub group status updated!']);
    }

    public function active(Request $request)
    {
        $query = ItemSubGroup::where('status', 1);

        if ($request->filled('item_group_id')) {
            $query->where('item_group_id', $request->item_group_id);
        }

        $rows = $query->orderBy('name')->get(['id', 'item_group_id', 'name']);

        return response()->json(['success' => true, 'data' => $rows]);
    }
}
