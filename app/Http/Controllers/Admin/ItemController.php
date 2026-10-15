<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $panel = $user->role->role_name === 'Manager' ? 'manager' : 'admin';
        return view('admin.items.index', compact('panel'));
    }

    public function fetch(Request $request)
    {
        try {
            $query = Item::with(['category', 'type', 'group']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('item_code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            }

            if ($request->filled('item_category_id')) $query->where('item_category_id', $request->item_category_id);
            if ($request->filled('item_group_id')) $query->where('item_group_id', $request->item_group_id);
            if ($request->filled('status') && $request->status !== 'all') $query->where('status', $request->status);

            $rows = $query->orderBy('id', 'desc')->paginate(10);

            $data = $rows->map(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'item_code' => $row->item_code ?? '-',
                    'category_name' => $row->category->name ?? '-',
                    'group_name' => $row->group->name ?? '-',
                    'type_name' => $row->type->name ?? '-',
                    'stock_type' => $row->stock_type,
                    'sales_price' => number_format($row->sales_price, 2),
                    'status' => (int) $row->status,
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
            return response()->json(['success' => false, 'message' => 'Failed to load items.', 'error' => $e->getMessage()], 500);
        }
    }

    public function get(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_VIEW')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $row = Item::with(['category', 'subCategory', 'group', 'subGroup', 'type'])->find($id);
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        return response()->json(['success' => true, 'data' => $row]);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_ADD')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'item_category_id' => 'required|exists:item_categories,id',
            'stock_type' => 'required|string',
            'unit_measure' => 'required|string',
            'cost_price' => 'required|numeric|min:0',
            'sales_price' => 'required|numeric|min:0',
            'client_description' => 'required|string',
            'office_description' => 'required|string',
            'item_code' => 'nullable|string|max:50|unique:items,item_code',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        Item::create($request->all());

        return response()->json(['success' => true, 'message' => 'Item created successfully!']);
    }

    public function update(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $row = Item::find($id);
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'item_category_id' => 'required|exists:item_categories,id',
            'stock_type' => 'required|string',
            'unit_measure' => 'required|string',
            'cost_price' => 'required|numeric|min:0',
            'sales_price' => 'required|numeric|min:0',
            'client_description' => 'required|string',
            'office_description' => 'required|string',
            'item_code' => ['nullable', 'string', 'max:50', Rule::unique('items', 'item_code')->ignore($row->id)],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $row->update($request->all());

        return response()->json(['success' => true, 'message' => 'Item updated successfully!']);
    }

    public function destroy(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_DELETE')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $row = Item::find($id);
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        // GRN relation protection goes here later

        $row->delete();
        return response()->json(['success' => true, 'message' => 'Item deleted!']);
    }

    public function toggleStatus(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('ITEM_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $row = Item::find($id);
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $row->status = $row->status == 1 ? 0 : 1;
        $row->save();

        return response()->json(['success' => true, 'message' => 'Status updated!']);
    }
}
