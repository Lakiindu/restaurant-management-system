<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // ==========================================
    // EXISTING RECEIPT & DAILY PAYMENTS METHODS
    // ==========================================

    public function printReceipt(int $id, Request $request)
    {
        $order = Order::with(['table', 'waiter', 'items.menuItem', 'payments'])->findOrFail($id);

        // Get size from URL, default to 80mm
        $size = $request->query('size', '80mm');

        if ($size === 'a4') {
            $pdf = Pdf::loadView('pdf.invoice', compact('order', 'size'))
                ->setPaper('A4', 'portrait');
        } else {
            $pdf = Pdf::loadView('pdf.receipt', compact('order', 'size'))
                ->setPaper([0, 0, 226.77, 800], 'portrait');
        }

        return $pdf->stream('Order-' . $order->order_no . '.pdf');
    }

    public function dailyPayments(Request $request)
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));

        $orders = Order::with(['table', 'waiter', 'payments'])
            ->where('status', 'Paid')
            ->whereDate('created_at', $date)
            ->get();

        $totalOrders = $orders->count();
        $subTotal    = $orders->sum('sub_total');
        $totalVat    = $orders->sum('vat_amount');

        // Aggregate exact payments collected for this date
        $payments = OrderPayment::whereHas('order', function ($q) use ($date) {
            $q->where('status', 'Paid')->whereDate('created_at', $date);
        })
            ->selectRaw('payment_method, SUM(amount) as total_amount')
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $cashTotal   = (float)($payments->get('Cash')->total_amount ?? 0);
        $cardTotal   = (float)($payments->get('Card')->total_amount ?? 0);
        $onlineTotal = (float)($payments->get('Online')->total_amount ?? 0);

        // Total Revenue = Actual Money Received Across All Methods
        $totalSales  = $cashTotal + $cardTotal + $onlineTotal;

        return view('admin.reports.daily_payments', compact(
            'date',
            'orders',
            'totalOrders',
            'totalSales',
            'subTotal',
            'totalVat',
            'cashTotal',
            'cardTotal',
            'onlineTotal'
        ));
    }

    public function dailyPaymentsPdf(Request $request)
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));

        $orders = Order::with(['table', 'waiter', 'payments'])
            ->where('status', 'Paid')
            ->whereDate('created_at', $date)
            ->get();

        $totalOrders = $orders->count();
        $subTotal    = $orders->sum('sub_total');
        $totalVat    = $orders->sum('vat_amount');

        $payments = OrderPayment::whereHas('order', function ($q) use ($date) {
            $q->where('status', 'Paid')->whereDate('created_at', $date);
        })
            ->selectRaw('payment_method, SUM(amount) as total_amount')
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $cashTotal   = (float)($payments->get('Cash')->total_amount ?? 0);
        $cardTotal   = (float)($payments->get('Card')->total_amount ?? 0);
        $onlineTotal = (float)($payments->get('Online')->total_amount ?? 0);

        $totalSales  = $cashTotal + $cardTotal + $onlineTotal;

        $pdf = Pdf::loadView('pdf.daily_payments_pdf', compact(
            'date',
            'orders',
            'totalOrders',
            'totalSales',
            'subTotal',
            'totalVat',
            'cashTotal',
            'cardTotal',
            'onlineTotal'
        ))->setPaper('A4', 'portrait');

        return $pdf->stream('Daily-Sales-Summary-' . $date . '.pdf');
    }

    // ==========================================
    // 1. ORDERS REPORT
    // ==========================================

    public function ordersReport(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate   = $request->get('end_date', date('Y-m-d'));
        $status    = $request->get('status');
        $orderType = $request->get('order_type');
        $waiterId  = $request->get('waiter_id');

        $query = Order::with(['table', 'waiter', 'payments'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($orderType)) {
            $query->where('order_type', $orderType);
        }

        if (!empty($waiterId)) {
            $query->where('waiter_id', $waiterId);
        }

        $orders = $query->latest()->get();

        // Calculate KPIs
        $kpis = [
            'total_orders'     => $orders->count(),
            'total_revenue'    => $orders->whereIn('status', ['Paid', 'Completed'])->sum('grand_total'),
            'paid_orders'      => $orders->whereIn('status', ['Paid', 'Completed'])->count(),
            'pending_orders'   => $orders->whereIn('status', ['Pending', 'Preparing', 'Ready'])->count(),
            'cancelled_orders' => $orders->where('status', 'Cancelled')->count(),
        ];

        $waiters = User::whereHas('role', function ($q) {
            $q->whereIn('role_name', ['Waiter', 'Admin', 'Manager']);
        })->get();

        return view('admin.reports.orders', compact('orders', 'kpis', 'startDate', 'endDate', 'status', 'orderType', 'waiterId', 'waiters'));
    }

    public function ordersReportPdf(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate   = $request->get('end_date', date('Y-m-d'));
        $status    = $request->get('status');
        $orderType = $request->get('order_type');
        $waiterId  = $request->get('waiter_id');

        $query = Order::with(['table', 'waiter'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if (!empty($status)) {
            $query->where('status', $status);
        }
        if (!empty($orderType)) {
            $query->where('order_type', $orderType);
        }
        if (!empty($waiterId)) {
            $query->where('waiter_id', $waiterId);
        }

        $orders = $query->latest()->get();

        $totalRevenue = $orders->whereIn('status', ['Paid', 'Completed'])->sum('grand_total');

        $pdf = Pdf::loadView('pdf.orders_report_pdf', compact('orders', 'startDate', 'endDate', 'totalRevenue'))
            ->setPaper('A4', 'landscape');

        return $pdf->stream('Orders_Report_' . $startDate . '_to_' . $endDate . '.pdf');
    }

    // ==========================================
    // 2. INVOICE REPORT
    // ==========================================

    public function invoicesReport(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate   = $request->get('end_date', date('Y-m-d'));
        $invoiceNo = $request->get('invoice_no');

        $query = Order::with(['table', 'waiter', 'payments'])
            ->whereIn('status', ['Paid', 'Completed'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if (!empty($invoiceNo)) {
            $query->where('order_no', 'LIKE', "%{$invoiceNo}%");
        }

        $invoices = $query->latest()->get();

        $kpis = [
            'total_invoices' => $invoices->count(),
            'sub_total'      => $invoices->sum('sub_total'),
            'total_vat'      => $invoices->sum('vat_amount'),
            'total_discount' => $invoices->sum('discount_amount'),
            'grand_total'    => $invoices->sum('grand_total'),
        ];

        return view('admin.reports.invoices', compact('invoices', 'kpis', 'startDate', 'endDate', 'invoiceNo'));
    }

    public function invoicesReportPdf(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate   = $request->get('end_date', date('Y-m-d'));
        $invoiceNo = $request->get('invoice_no');

        $query = Order::with(['table', 'waiter', 'payments'])
            ->whereIn('status', ['Paid', 'Completed'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if (!empty($invoiceNo)) {
            $query->where('order_no', 'LIKE', "%{$invoiceNo}%");
        }

        $invoices = $query->latest()->get();

        $pdf = Pdf::loadView('pdf.invoices_report_pdf', compact('invoices', 'startDate', 'endDate'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream('Invoice_Report_' . $startDate . '_to_' . $endDate . '.pdf');
    }

    // ==========================================
    // 3. PAYMENT RECEIPTS REPORT
    // ==========================================

    public function paymentsReport(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate   = $request->get('end_date', date('Y-m-d'));
        $method    = $request->get('payment_method');

        $query = OrderPayment::with(['order', 'order.waiter'])
            ->whereHas('order', function ($q) {
                $q->whereIn('status', ['Paid', 'Completed']);
            })
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if (!empty($method)) {
            $query->where('payment_method', $method);
        }

        $payments = $query->latest()->get();

        $kpis = [
            'total_collected' => $payments->sum('amount'),
            'cash_collected'  => $payments->where('payment_method', 'Cash')->sum('amount'),
            'card_collected'  => $payments->where('payment_method', 'Card')->sum('amount'),
            'online_collected' => $payments->where('payment_method', 'Online')->sum('amount'),
            'change_given'    => $payments->sum('change_given'),
        ];

        return view('admin.reports.payments', compact('payments', 'kpis', 'startDate', 'endDate', 'method'));
    }

    public function paymentsReportPdf(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate   = $request->get('end_date', date('Y-m-d'));
        $method    = $request->get('payment_method');

        $query = OrderPayment::with(['order', 'order.waiter'])
            ->whereHas('order', function ($q) {
                $q->whereIn('status', ['Paid', 'Completed']);
            })
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if (!empty($method)) {
            $query->where('payment_method', $method);
        }

        $payments = $query->latest()->get();

        $pdf = Pdf::loadView('pdf.payments_report_pdf', compact('payments', 'startDate', 'endDate', 'method'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream('Payment_Receipts_Report_' . $startDate . '_to_' . $endDate . '.pdf');
    }

    // ==========================================
    // 4. STOCK & CONSUMPTION REPORT
    // ==========================================
    public function stockReport(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $statusFilter = $request->get('stock_status'); // 'low', 'out', 'ok'

        // Dynamically detect column name in item_categories
        $catCol = 'name';
        if (\Illuminate\Support\Facades\Schema::hasTable('item_categories')) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('item_categories', 'category_name')) {
                $catCol = 'category_name';
            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('item_categories', 'name')) {
                $catCol = 'name';
            }
        }

        // 1. Current Inventory Items
        $items = DB::table('items')
            ->leftJoin('item_categories', 'items.item_category_id', '=', 'item_categories.id')
            ->select(
                'items.*',
                DB::raw("COALESCE(item_categories.{$catCol}, 'Uncategorized') as category_name")
            )
            ->get()
            ->map(function ($item) {
                if ($item->current_stock <= 0) {
                    $item->stock_status = 'Out of Stock';
                    $item->badge_class = 'bg-danger';
                } elseif ($item->current_stock <= $item->reorder_level) {
                    $item->stock_status = 'Low Stock';
                    $item->badge_class = 'bg-warning text-dark';
                } else {
                    $item->stock_status = 'In Stock';
                    $item->badge_class = 'bg-success';
                }
                return $item;
            });

        if ($statusFilter === 'low') {
            $items = $items->where('stock_status', 'Low Stock');
        } elseif ($statusFilter === 'out') {
            $items = $items->where('stock_status', 'Out of Stock');
        } elseif ($statusFilter === 'ok') {
            $items = $items->where('stock_status', 'In Stock');
        }

        // 2. Ingredients Consumption in date range
        $consumption = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('menu_item_ingredients', 'order_items.menu_item_id', '=', 'menu_item_ingredients.menu_item_id')
            ->join('items', 'menu_item_ingredients.inventory_item_id', '=', 'items.id')
            ->whereIn('orders.status', ['Paid', 'Completed'])
            ->whereDate('orders.created_at', '>=', $startDate)
            ->whereDate('orders.created_at', '<=', $endDate)
            ->select(
                'items.name as item_name',
                'items.unit_measure',
                DB::raw('SUM(order_items.quantity * menu_item_ingredients.quantity) as total_consumed')
            )
            ->groupBy('items.id', 'items.name', 'items.unit_measure')
            ->get();

        $kpis = [
            'total_items'     => DB::table('items')->count(),
            'low_stock_count' => DB::table('items')->whereRaw('current_stock <= reorder_level AND current_stock > 0')->count(),
            'out_of_stock'    => DB::table('items')->where('current_stock', '<=', 0)->count(),
            'total_valuation' => DB::table('items')->select(DB::raw('SUM(current_stock * cost_price) as total_val'))->value('total_val') ?? 0,
        ];

        return view('admin.reports.stock', compact('items', 'consumption', 'kpis', 'startDate', 'endDate', 'statusFilter'));
    }

    public function stockReportPdf(Request $request)
    {
        +$startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        $catCol = 'name';
        if (\Illuminate\Support\Facades\Schema::hasTable('item_categories')) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('item_categories', 'category_name')) {
                $catCol = 'category_name';
            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('item_categories', 'name')) {
                $catCol = 'name';
            }
        }

        $items = DB::table('items')
            ->leftJoin('item_categories', 'items.item_category_id', '=', 'item_categories.id')
            ->select(
                'items.*',
                DB::raw("COALESCE(item_categories.{$catCol}, 'Uncategorized') as category_name")
            )
            ->get();

        $consumption = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('menu_item_ingredients', 'order_items.menu_item_id', '=', 'menu_item_ingredients.menu_item_id')
            ->join('items', 'menu_item_ingredients.inventory_item_id', '=', 'items.id')
            ->whereIn('orders.status', ['Paid', 'Completed'])
            ->whereDate('orders.created_at', '>=', $startDate)
            ->whereDate('orders.created_at', '<=', $endDate)
            ->select(
                'items.name as item_name',
                'items.unit_measure',
                DB::raw('SUM(order_items.quantity * menu_item_ingredients.quantity) as total_consumed')
            )
            ->groupBy('items.id', 'items.name', 'items.unit_measure')
            ->get();

        $pdf = Pdf::loadView('pdf.stock_report_pdf', compact('items', 'consumption', 'startDate', 'endDate'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream('Stock_Report_' . $startDate . '_to_' . $endDate . '.pdf');
    }
}
