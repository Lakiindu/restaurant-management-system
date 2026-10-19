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

    public function orderScreen(int $table_id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasPagePermission('POS_TERMINAL')) {
            abort(403, 'Unauthorized access.');
        }

        $table = DiningTable::findOrFail($table_id);

        $categories = MenuCategory::orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();
        $menuItems  = MenuItem::with('category')->orderBy('name', 'asc')->get();

        $activeOrder = Order::with('items.menuItem', 'payments')
            ->where('table_id', $table->id)
            ->whereIn('status', ['Parked', 'Pending', 'Preparing', 'Ready'])
            ->first();

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
            'table_id'   => 'required|exists:dining_tables,id',
            'status'     => 'required|in:Parked,Pending',
            'items'      => 'required|array|min:1',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $table = DiningTable::lockForUpdate()->findOrFail($request->table_id);

            // Existing open order for this table?
            $order = Order::where('table_id', $table->id)
                ->whereIn('status', ['Parked', 'Pending', 'Preparing', 'Ready'])
                ->lockForUpdate()
                ->first();

            $subTotal = 0;
            foreach ($request->items as $row) {
                $subTotal += ((float) $row['price']) * ((int) $row['qty']);
            }
            $vatAmount   = round($subTotal * 0.18, 2); // 18% SL VAT
            $grandTotal  = round($subTotal + $vatAmount, 2);

            if (!$order) {
                // Generate order number inside transaction
                $nextId  = (Order::max('id') ?? 0) + 1;
                $orderNo = 'ORD-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

                $order = Order::create([
                    'order_no'         => $orderNo,
                    'table_id'         => $table->id,
                    'waiter_id'        => Auth::user()->user_id ?? Auth::id(),
                    'order_type'       => 'Dine-in',
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

            // Table status
            if ($request->status === 'Pending' || $request->status === 'Parked') {
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
    public function checkout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasPagePermission('POS_TERMINAL')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Validate that we are receiving an array of payments (handles split bills!)
        $request->validate([
            'order_id'          => 'required|exists:orders,id',
            'discount_amount'   => 'nullable|numeric|min:0',
            'payments'          => 'required|array|min:1',
            'payments.*.method' => 'required|string|in:Cash,Card,Online',
            'payments.*.amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Lock order to prevent double-billing
            $order = Order::lockForUpdate()->findOrFail($request->order_id);

            if ($order->status === 'Paid') {
                return response()->json(['success' => false, 'message' => 'Order is already paid.'], 400);
            }

            // 1. Calculate final totals (Apply discount if any)
            $discount = (float) ($request->discount_amount ?? 0);
            $finalTotal = $order->sub_total + $order->vat_amount - $discount;

            // 2. Calculate Total Paid from the array of payments
            $totalPaid = 0;
            foreach ($request->payments as $payment) {
                $totalPaid += (float) $payment['amount'];
            }

            // 3. Ensure they paid enough
            if (round($totalPaid, 2) < round($finalTotal, 2)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient payment. Outstanding balance remains.'
                ], 400);
            }

            $change = $totalPaid - $finalTotal;

            // 4. Save each payment row (This is where the Split Bill magic happens)
            foreach ($request->payments as $index => $payment) {
                // Only give change on the Cash portion (usually the last payment processed)
                $changeGiven = ($payment['method'] === 'Cash' && $index === count($request->payments) - 1) ? $change : 0;

                \App\Models\OrderPayment::create([
                    'order_id'       => $order->id,
                    'payment_method' => $payment['method'],
                    'amount'         => (float) $payment['amount'],
                    'change_given'   => $changeGiven
                ]);
            }

            // 5. Update Order Status
            $order->update([
                'status'          => 'Paid',
                'discount_amount' => $discount,
                'grand_total'     => $finalTotal
            ]);

            // 6. Free up the Dining Table! Make it Green again.
            $table = \App\Models\DiningTable::find($order->table_id);
            if ($table) {
                $table->update(['status' => 'Available']);
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Payment successful! Table cleared.',
                'order_id' => $order->id,
                'change'   => $change,
                // Optional: redirect to receipt printing later (Phase 5)
                'redirect' => url($this->panel() . '/pos')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function panel(): string
    {
        return strtolower(Auth::user()->role->role_name ?? 'admin');
    }
}
