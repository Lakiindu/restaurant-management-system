<?php

namespace App\Http\Controllers;

use App\Models\DiningTable;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasPagePermission('POS_TERMINAL')) {
            abort(403, 'Unauthorized access to POS Terminal.');
        }

        $tables = DiningTable::with(['activeOrder' => function ($q) {
            $q->whereIn('status', ['Parked', 'Pending', 'Preparing', 'Ready']);
        }])->orderBy('id', 'asc')->get();

        return view('pos.index', compact('tables'));
    }

    /**
     * 1. When user clicks a Table on Floor Plan
     */
    public function openTableOrder(int $tableId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $table = DiningTable::findOrFail($tableId);

        // Check if table already has an active unpaid order
        $order = Order::where('table_id', $tableId)
            ->whereNotIn('status', ['Paid', 'Cancelled', 'Completed'])
            ->first();

        // If no active order, create a new Dine-in order immediately
        if (!$order) {
            $order = Order::create([
                'order_no'        => 'ORD-' . str_pad((Order::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT),
                'waiter_id'       => $user->id,
                'table_id'        => $tableId,
                'order_type'      => 'Dine-in',
                'status'          => 'Pending',
                'sub_total'       => 0,
                'vat_amount'      => 0,
                'discount_amount' => 0,
                'grand_total'     => 0,
            ]);

            $table->update(['status' => 'Occupied']);
        }

        $prefix = $user->role_id == 1 ? 'admin' : ($user->role_id == 2 ? 'manager' : 'waiter');

        return redirect()->route($prefix . '.pos.order', $order->id);
    }

    /**
     * 2. When user clicks "+ New Takeaway Order"
     */
    public function createTakeawayOrder(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Create a new takeaway order without a table
        $order = Order::create([
            'order_no'        => 'ORD-' . str_pad((Order::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT),
            'waiter_id'       => $user->id,
            'table_id'        => null, // No table for takeaway
            'order_type'      => 'Takeaway',
            'status'          => 'Pending',
            'sub_total'       => 0,
            'vat_amount'      => 0,
            'discount_amount' => 0,
            'grand_total'     => 0,
        ]);

        // Redirect to the POS workspace for this new order
        $prefix = $user->role_id == 1 ? 'admin' : ($user->role_id == 2 ? 'manager' : 'waiter');

        return redirect()->route($prefix . '.pos.order', $order->id);
    }

    /**
     * 3. Order Screen Workspace (Loads Order directly by Order ID!)
     */
    public function orderScreen(int $orderId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasPagePermission('POS_TERMINAL')) {
            abort(403, 'Unauthorized access.');
        }

        // Find Order by Order ID (Works for both Dine-In & Takeaway)
        $activeOrder = Order::with(['table', 'waiter', 'items.menuItem', 'payments'])->findOrFail($orderId);

        // Retain table variable for views to consume safely (will be null for takeaway)
        $table = $activeOrder->table;

        // Fetch categories/menu items retaining existing model structure
        $categories = MenuCategory::orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();
        $menuItems  = MenuItem::with('category')->orderBy('name', 'asc')->get();

        return view('pos.order', compact('table', 'categories', 'menuItems', 'activeOrder'));
    }

    /**
     * Hold Order (Parked) or Send to Kitchen (Pending)
     */
    public function saveOrder(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasPagePermission('POS_TERMINAL')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'order_id'   => 'nullable|exists:orders,id',
            'table_id'   => 'nullable|exists:dining_tables,id', // Nullable for takeaway
            'status'     => 'required|in:Parked,Pending',
            'items'      => 'required|array|min:1',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $table = null;
            if ($request->table_id) {
                $table = DiningTable::lockForUpdate()->find($request->table_id);
            }

            // Existing open order check: Prioritize order_id if passed, fallback to table
            $order = null;
            if ($request->filled('order_id')) {
                $order = Order::lockForUpdate()->find($request->order_id);
            } elseif ($table) {
                $order = Order::where('table_id', $table->id)
                    ->whereIn('status', ['Parked', 'Pending', 'Preparing', 'Ready'])
                    ->lockForUpdate()
                    ->first();
            }

            $subTotal = 0;
            foreach ($request->items as $row) {
                $subTotal += ((float) $row['price']) * ((int) $row['qty']);
            }
            $vatAmount   = round($subTotal * 0.18, 2); // 18% SL VAT
            $grandTotal  = round($subTotal + $vatAmount, 2);

            if (!$order) {
                // Generate order number inside transaction (fallback scenario)
                $nextId  = (Order::max('id') ?? 0) + 1;
                $orderNo = 'ORD-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

                $order = Order::create([
                    'order_no'         => $orderNo,
                    'table_id'         => $table ? $table->id : null,
                    'waiter_id'        => Auth::user()->user_id ?? Auth::id(),
                    'order_type'       => $table ? 'Dine-in' : 'Takeaway',
                    'status'           => $request->status,
                    'sub_total'        => $subTotal,
                    'vat_amount'       => $vatAmount,
                    'discount_amount'  => 0,
                    'grand_total'      => $grandTotal,
                    'notes'            => $request->notes ?? null,
                ]);
            } else {
                $order->update([
                    'status'          => $request->status,
                    'sub_total'       => $subTotal,
                    'vat_amount'      => $vatAmount,
                    'grand_total'     => $grandTotal,
                    'waiter_id'       => Auth::user()->user_id ?? Auth::id(),
                ]);

                // Replace line items
                OrderItem::where('order_id', $order->id)->delete();
            }

            foreach ($request->items as $row) {
                $lineTotal = ((float) $row['price']) * ((int) $row['qty']);
                OrderItem::create([
                    'order_id'       => $order->id,
                    'menu_item_id'   => $row['id'],
                    'quantity'       => (int) $row['qty'],
                    'price'          => (float) $row['price'],
                    'total'          => $lineTotal,
                    'kitchen_status' => $request->status === 'Pending' ? 'Pending' : 'Pending',
                    'notes'          => $row['notes'] ?? null,
                ]);
            }

            // Table status update
            if ($table && ($request->status === 'Pending' || $request->status === 'Parked')) {
                $table->update(['status' => 'Occupied']);
            }

            DB::commit();

            $msg = $request->status === 'Parked'
                ? 'Order held successfully!'
                : 'Order sent to kitchen!';

            return response()->json([
                'success'   => true,
                'message'   => $msg,
                'order_no'  => $order->order_no,
                'order_id'  => $order->id,
                'status'    => $order->status,
                'redirect'  => url(($this->panel()) . '/pos'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process Checkout & Split Bill Payments
     */
    public function checkout(Request $request, int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasPagePermission('POS_TERMINAL')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $order = Order::findOrFail($id);
        if ($order->status === 'Paid') {
            return response()->json([
                'success' => false,
                'is_already_paid' => true,
                'message' => 'Order is already paid.',
                'order_id' => $order->id
            ], 400);
        }

        $request->validate([
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|string|in:Cash,Card,Online',
            'payments.*.amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 1. SAVE / SYNC CART ITEMS IF PASSED FROM FRONTEND
            if ($request->has('items') && is_array($request->items) && count($request->items) > 0) {
                // Remove old empty/pending draft items for this order
                DB::table('order_items')->where('order_id', $order->id)->delete();
                $subTotal = 0;
                foreach ($request->items as $item) {
                    $lineTotal = (float)$item['price'] * (int)$item['quantity'];
                    $subTotal += $lineTotal;
                    DB::table('order_items')->insert([
                        'order_id'       => $order->id,
                        'menu_item_id'   => $item['menu_item_id'],
                        'quantity'       => $item['quantity'],
                        'price'          => $item['price'],
                        'total'          => $lineTotal,
                        'kitchen_status' => 'Pending', // Ready for Kitchen!
                        'notes'          => $item['notes'] ?? null,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
                $vatAmount = $subTotal * 0.18; // 18% VAT
                $grandTotal = $subTotal + $vatAmount;
                $order->update([
                    'sub_total'   => $subTotal,
                    'vat_amount'  => $vatAmount,
                    'grand_total' => $grandTotal,
                ]);
            } else {
                // Ensure existing items have kitchen_status = 'Pending' if null
                DB::table('order_items')
                    ->where('order_id', $order->id)
                    ->whereNull('kitchen_status')
                    ->update(['kitchen_status' => 'Pending']);
            }

            // Reload fresh order with items
            $order->load('items');

            // 2. PROCESS PAYMENTS
            $totalPaid = 0;
            foreach ($request->payments as $p) {
                $totalPaid += (float)$p['amount'];
                \App\Models\OrderPayment::create([
                    'order_id'       => $order->id,
                    'payment_method' => $p['method'],
                    'amount'         => $p['amount'],
                    'change_given'   => 0,
                ]);
            }

            $changeGiven = max(0, $totalPaid - $order->grand_total);
            $order->update(['status' => 'Paid']);

            // 3. AUTO DEDUCT INVENTORY INGREDIENTS FROM RECIPES
            foreach ($order->items as $orderItem) {
                $ingredients = DB::table('menu_item_ingredients')
                    ->where('menu_item_id', $orderItem->menu_item_id)
                    ->get();

                foreach ($ingredients as $ingredient) {
                    $totalDeduct = (float) $ingredient->quantity * (float) $orderItem->quantity;

                    DB::table('items')
                        ->where('id', $ingredient->inventory_item_id)
                        ->decrement('current_stock', $totalDeduct);
                }
            }

            // 4. FREE TABLE IF DINE-IN
            if ($order->table_id) {
                DB::table('dining_tables')
                    ->where('id', $order->table_id)
                    ->update(['status' => 'Available']);
            }

            DB::commit();

            return response()->json([
                'success'       => true,
                'message'       => 'Payment successful',
                'order_id'      => $order->id,
                'change_due'    => $changeGiven,
                'payment_count' => count($request->payments),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Checkout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function panel(): string
    {
        return strtolower(Auth::user()->role->role_name ?? 'admin');
    }
}
