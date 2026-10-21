<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KitchenController extends Controller
{
    /**
     * Display the Chef Dashboard
     */
    public function dashboard()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasPagePermission('CHEF_DASHBOARD')) {
            abort(403, 'Unauthorized access.');
        }

        $panel = strtolower($user->role->role_name ?? 'admin');

        // Simple Kitchen Stats
        $pendingItems = OrderItem::where('kitchen_status', 'Pending')->count();
        $preparingItems = OrderItem::where('kitchen_status', 'Preparing')->count();
        $readyItems = OrderItem::where('kitchen_status', 'Ready')->whereDate('updated_at', today())->count();

        return view('kitchen.dashboard', compact('panel', 'pendingItems', 'preparingItems', 'readyItems'));
    }

    /**
     * Display the Kitchen Dashboard (KDS)
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasPagePermission('KITCHEN_DISPLAY')) {
            abort(403, 'Unauthorized access to Kitchen Display.');
        }

        $panel = strtolower($user->role->role_name ?? 'admin');

        // Fetch Orders that need kitchen attention (Oldest orders first)
        $orders = Order::with(['table', 'items.menuItem'])
            ->whereIn('status', ['Pending', 'Preparing'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kitchen.index', compact('orders', 'panel'));
    }

    /**
     * Chef updates an individual line item (Pending -> Preparing -> Ready)
     */
    public function updateItemStatus(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasPagePermission('KITCHEN_DISPLAY')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'status'  => 'required|in:Preparing,Ready,Served',
        ]);

        $item = OrderItem::findOrFail($request->item_id);
        $item->update(['kitchen_status' => $request->status]);

        // Evaluate parent Order status based on all line items
        $order = Order::with('items')->find($item->order_id);

        $allItemsReady = true;
        $anyPreparing  = false;

        foreach ($order->items as $i) {
            if ($i->kitchen_status === 'Preparing') {
                $anyPreparing = true;
            }
            if (in_array($i->kitchen_status, ['Pending', 'Preparing'])) {
                $allItemsReady = false;
            }
        }

        if ($allItemsReady) {
            $order->update(['status' => 'Ready']);
        } elseif ($anyPreparing && $order->status === 'Pending') {
            $order->update(['status' => 'Preparing']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kitchen status updated to ' . $request->status,
        ]);
    }

    /**
     * Display Kitchen History (Orders cooked today)
     */
    public function history()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasPagePermission('KITCHEN_HISTORY')) {
            abort(403, 'Unauthorized access to Kitchen History.');
        }

        $panel = strtolower($user->role->role_name ?? 'admin');

        // Fetch Orders that are Ready, Paid, or Completed for TODAY
        $orders = Order::with(['table', 'items.menuItem'])
            ->whereIn('status', ['Ready', 'Paid', 'Completed'])
            ->whereDate('updated_at', today())
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('kitchen.history', compact('orders', 'panel'));
    }
}
