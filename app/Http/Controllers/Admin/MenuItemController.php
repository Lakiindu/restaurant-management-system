<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MenuItemController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $panel = $user->role->role_name === 'Manager' ? 'manager' : 'admin';
        return view('admin.menu-items.index', compact('panel'));
    }

    public function fetchItems(Request $request)
    {
        $query = MenuItem::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_vegetarian') && $request->is_vegetarian !== 'all') {
            $query->where('is_vegetarian', $request->is_vegetarian);
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(10);

        $data = $items->map(function ($item) {
            return [
                'item_id' => $item->item_id,
                'item_name' => $item->item_name,
                'item_code' => $item->item_code,
                'price' => $item->price,
                'category_name' => $item->category->category_name ?? 'N/A',
                'is_vegetarian' => $item->is_vegetarian,
                'status' => $item->status,
                'created_at' => \Carbon\Carbon::parse($item->created_at)->format('M d, Y'),
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

        if (!$user->hasOptionPermission('MENU_ITEM_VIEW')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $item = MenuItem::with('category')->find($id);

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
                'category_id' => $item->category_id,
                'category_name' => $item->category->category_name ?? 'N/A',
                'description' => $item->description,
                'is_vegetarian' => $item->is_vegetarian,
                'status' => $item->status,
                'created_at' => \Carbon\Carbon::parse($item->created_at)->format('M d, Y h:i A'),
            ]
        ]);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('MENU_ITEM_ADD')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:100',
            'item_code' => 'required|string|max:50|unique:menu_items,item_code|regex:/^[A-Z0-9_-]+$/',
            'category_id' => 'required|exists:menu_categories,category_id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'is_vegetarian' => 'required|in:0,1',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        MenuItem::create([
            'item_name' => $request->item_name,
            'item_code' => strtoupper($request->item_code),
            'category_id' => $request->category_id,
            'price' => $request->price,
            'description' => $request->description,
            'is_vegetarian' => $request->is_vegetarian,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Menu item created successfully!']);
    }

    public function update(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('MENU_ITEM_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $item = MenuItem::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:100',
            'item_code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('menu_items', 'item_code')->ignore($item->item_id, 'item_id')],
            'category_id' => 'required|exists:menu_categories,category_id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'is_vegetarian' => 'required|in:0,1',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $item->update([
            'item_name' => $request->item_name,
            'item_code' => strtoupper($request->item_code),
            'category_id' => $request->category_id,
            'price' => $request->price,
            'description' => $request->description,
            'is_vegetarian' => $request->is_vegetarian,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Menu item updated successfully!']);
    }

    public function destroy(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('MENU_ITEM_DELETE')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $item = MenuItem::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $item->delete();
        return response()->json(['success' => true, 'message' => 'Menu item deleted successfully!']);
    }

    public function toggleStatus(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('MENU_ITEM_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $item = MenuItem::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $item->status = $item->status == 1 ? 0 : 1;
        $item->save();

        return response()->json(['success' => true, 'message' => "Item status updated!"]);
    }
}
