<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class InventoryItemController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $panel = $user->role->role_name === 'Manager' ? 'manager' : 'admin';
        return view('admin.inventory-items.index', compact('panel'));
    }

    public function fetchItems(Request $request)
    {
        $query = InventoryItem::with('type');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('stock_status') && $request->stock_status === 'low') {
            $query->whereColumn('current_stock', '<=', 'minimum_stock');
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(10);

        $data = $items->map(function ($item) {
            return [
                'item_id' => $item->item_id,
                'item_name' => $item->item_name,
                'item_code' => $item->item_code,
                'price' => $item->price,
                'unit' => $item->unit,
                'type_name' => $item->type->type_name ?? 'N/A',
                'current_stock' => $item->current_stock,
                'minimum_stock' => $item->minimum_stock,
                'is_low_stock' => $item->current_stock <= $item->minimum_stock,
                'status' => $item->status,
                'created_at' => Carbon::parse($item->created_at)->format('M d, Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ]
        ]);
    }

    public function getItem(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('INV_ITEM_VIEW')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $item = InventoryItem::with('type')->find($id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'item_id' => $item->item_id,
                'item_name' => $item->item_name,
                'item_code' => $item->item_code,
                'price' => $item->price,
                'type_id' => $item->type_id,
                'type_name' => $item->type->type_name ?? 'N/A',
                'unit' => $item->unit,
                'current_stock' => $item->current_stock,
                'minimum_stock' => $item->minimum_stock,
                'description' => $item->description,
                'status' => $item->status,
                'created_at' => Carbon::parse($item->created_at)->format('M d, Y h:i A'),
                'updated_at' => Carbon::parse($item->updated_at)->format('M d, Y h:i A'),
            ]
        ]);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('INV_ITEM_ADD')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - You cannot add items.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:100',
            'item_code' => 'required|string|max:50|unique:inventory_items,item_code|regex:/^[A-Z0-9_-]+$/',
            'price' => 'required|numeric|min:0',
            'type_id' => 'required|exists:inventory_types,type_id',
            'unit' => 'required|string|max:20',
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
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

        InventoryItem::create([
            'item_name' => $request->item_name,
            'item_code' => strtoupper($request->item_code),
            'price' => $request->price,
            'type_id' => $request->type_id,
            'unit' => $request->unit,
            'current_stock' => $request->current_stock,
            'minimum_stock' => $request->minimum_stock,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Inventory item created successfully!']);
    }

    public function update(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('INV_ITEM_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - You cannot edit items.'], 403);
        }

        $item = InventoryItem::find($id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:100',
            'item_code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('inventory_items', 'item_code')->ignore($item->item_id, 'item_id')],
            'price' => 'required|numeric|min:0',
            'type_id' => 'required|exists:inventory_types,type_id',
            'unit' => 'required|string|max:20',
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
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

        $item->update([
            'item_name' => $request->item_name,
            'item_code' => strtoupper($request->item_code),
            'price' => $request->price,
            'type_id' => $request->type_id,
            'unit' => $request->unit,
            'current_stock' => $request->current_stock,
            'minimum_stock' => $request->minimum_stock,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Inventory item updated successfully!']);
    }

    public function destroy(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('INV_ITEM_DELETE')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - You cannot delete items.'], 403);
        }

        $item = InventoryItem::find($id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $item->delete();

        return response()->json(['success' => true, 'message' => 'Inventory item deleted successfully!']);
    }

    public function toggleStatus(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('INV_ITEM_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - You cannot edit items.'], 403);
        }

        $item = InventoryItem::find($id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $item->status = $item->status == 1 ? 0 : 1;
        $item->save();

        return response()->json(['success' => true, 'message' => 'Inventory item status updated successfully!']);
    }
}
