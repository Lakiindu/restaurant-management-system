<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class InventoryTypeController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $panel = $user->role->role_name === 'Manager' ? 'manager' : 'admin';
        return view('admin.inventory-types.index', compact('panel'));
    }

    public function fetchTypes(Request $request)
    {
        $query = InventoryType::withCount('items');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('type_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $types = $query->orderBy('created_at', 'desc')->paginate(10);

        $data = $types->map(function ($type) {
            return [
                'type_id' => $type->type_id,
                'type_name' => $type->type_name,
                'description' => $type->description ?? '-',
                'items_count' => $type->items_count,
                'status' => $type->status,
                'created_at' => Carbon::parse($type->created_at)->format('M d, Y'),
                'updated_at' => Carbon::parse($type->updated_at)->format('M d, Y h:i A'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $types->currentPage(),
                'last_page' => $types->lastPage(),
                'per_page' => $types->perPage(),
                'total' => $types->total(),
                'from' => $types->firstItem(),
                'to' => $types->lastItem(),
            ]
        ]);
    }

    public function getType(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('INV_TYPE_VIEW')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $type = InventoryType::withCount('items')->find($id);

        if (!$type) {
            return response()->json(['success' => false, 'message' => 'Type not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'type_id' => $type->type_id,
                'type_name' => $type->type_name,
                'description' => $type->description,
                'items_count' => $type->items_count,
                'status' => $type->status,
                'created_at' => Carbon::parse($type->created_at)->format('M d, Y h:i A'),
                'updated_at' => Carbon::parse($type->updated_at)->format('M d, Y h:i A'),
            ]
        ]);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('INV_TYPE_ADD')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - You cannot add inventory types.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:50|unique:inventory_types,type_name',
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

        InventoryType::create([
            'type_name' => $request->type_name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Inventory type created successfully!']);
    }

    public function update(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('INV_TYPE_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - You cannot edit inventory types.'], 403);
        }

        $type = InventoryType::find($id);

        if (!$type) {
            return response()->json(['success' => false, 'message' => 'Type not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'type_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('inventory_types', 'type_name')->ignore($type->type_id, 'type_id')
            ],
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

        $type->update([
            'type_name' => $request->type_name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Inventory type updated successfully!']);
    }

    public function destroy(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('INV_TYPE_DELETE')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - You cannot delete inventory types.'], 403);
        }

        $type = InventoryType::find($id);

        if (!$type) {
            return response()->json(['success' => false, 'message' => 'Type not found'], 404);
        }

        $itemsCount = $type->items()->count();
        if ($itemsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete this type. {$itemsCount} item(s) are using it."
            ], 403);
        }

        $type->delete();

        return response()->json(['success' => true, 'message' => 'Inventory type deleted successfully!']);
    }

    public function toggleStatus(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('INV_TYPE_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - You cannot edit inventory types.'], 403);
        }

        $type = InventoryType::find($id);

        if (!$type) {
            return response()->json(['success' => false, 'message' => 'Type not found'], 404);
        }

        $type->status = $type->status == 1 ? 0 : 1;
        $type->save();

        return response()->json([
            'success' => true,
            'message' => 'Inventory type status updated successfully!'
        ]);
    }

    public function getActiveTypes()
    {
        $types = InventoryType::where('status', 1)
            ->orderBy('type_name')
            ->get(['type_id', 'type_name']);

        return response()->json(['success' => true, 'data' => $types]);
    }
}
