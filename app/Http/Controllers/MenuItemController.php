<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Item;
use App\Models\MenuItemIngredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasOptionPermission('MENU_ITEM_VIEW') && !$user->hasPagePermission('MENU_ITEM_LIST')) {
            abort(403, 'Unauthorized access.');
        }

        $query = MenuItem::with(['category', 'ingredients.inventoryItem']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        $menuItems = $query->orderBy('id', 'desc')->paginate(15);
        $categories = MenuCategory::where('status', 'Active')->orderBy('sort_order')->get();

        return view('admin.menu_items.index', compact('menuItems', 'categories'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasOptionPermission('MENU_ITEM_ADD')) {
            abort(403, 'Unauthorized access to create menu items.');
        }

        // FIX: Removed the strict ->where('status', 'Active') filter so all data loads
        $categories = MenuCategory::orderBy('sort_order', 'asc')->get();
        $inventoryItems = Item::orderBy('name', 'asc')->get();

        // Auto generate Code (e.g., MI-001)
        $nextId = (MenuItem::max('id') ?? 0) + 1;
        $autoCode = 'MI-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('admin.menu_items.create', compact('categories', 'inventoryItems', 'autoCode'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasOptionPermission('MENU_ITEM_ADD')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'item_code' => 'required|string|unique:menu_items,item_code',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:menu_categories,id',
            'price' => 'required|numeric|min:0',
            'is_vegetarian' => 'boolean',
            'status' => 'required|in:Active,Inactive',
            'ingredients' => 'nullable|array',
            'ingredients.*.inventory_item_id' => 'required|exists:items,id',
            'ingredients.*.quantity' => 'required|numeric|gt:0',
        ]);

        DB::beginTransaction();
        try {
            $menuItem = MenuItem::create([
                'item_code' => $request->item_code,
                'name' => $request->name,
                'category_id' => $request->category_id,
                'price' => $request->price,
                'description' => $request->description,
                'is_vegetarian' => $request->has('is_vegetarian') ? 1 : 0,
                'status' => $request->status,
            ]);

            if ($request->has('ingredients')) {
                foreach ($request->ingredients as $ingredient) {
                    if (!empty($ingredient['inventory_item_id']) && !empty($ingredient['quantity'])) {
                        MenuItemIngredient::create([
                            'menu_item_id' => $menuItem->id,
                            'inventory_item_id' => $ingredient['inventory_item_id'],
                            'quantity' => $ingredient['quantity'],
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Menu item created successfully with recipe!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function edit(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasOptionPermission('MENU_ITEM_EDIT')) {
            abort(403, 'Unauthorized access.');
        }

        $menuItem = MenuItem::with('ingredients.inventoryItem')->findOrFail($id);

        // FIX: Removed the strict ->where('status', 'Active') filter so all data loads
        $categories = MenuCategory::orderBy('sort_order', 'asc')->get();
        $inventoryItems = Item::orderBy('name', 'asc')->get();

        return view('admin.menu_items.edit', compact('menuItem', 'categories', 'inventoryItems'));
    }

    public function update(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasOptionPermission('MENU_ITEM_EDIT')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $menuItem = MenuItem::findOrFail($id);

        $request->validate([
            'item_code' => 'required|string|unique:menu_items,item_code,' . $id,
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:menu_categories,id',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:Active,Inactive',
            'ingredients' => 'nullable|array',
            'ingredients.*.inventory_item_id' => 'required|exists:items,id',
            'ingredients.*.quantity' => 'required|numeric|gt:0',
        ]);

        DB::beginTransaction();
        try {
            $menuItem->update([
                'item_code' => $request->item_code,
                'name' => $request->name,
                'category_id' => $request->category_id,
                'price' => $request->price,
                'description' => $request->description,
                'is_vegetarian' => $request->has('is_vegetarian') ? 1 : 0,
                'status' => $request->status,
            ]);

            // Sync recipe ingredients
            MenuItemIngredient::where('menu_item_id', $menuItem->id)->delete();

            if ($request->has('ingredients')) {
                foreach ($request->ingredients as $ingredient) {
                    if (!empty($ingredient['inventory_item_id']) && !empty($ingredient['quantity'])) {
                        MenuItemIngredient::create([
                            'menu_item_id' => $menuItem->id,
                            'inventory_item_id' => $ingredient['inventory_item_id'],
                            'quantity' => $ingredient['quantity'],
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Menu item updated successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasOptionPermission('MENU_ITEM_DELETE')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            $item = MenuItem::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'Menu item deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
