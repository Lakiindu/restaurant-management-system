<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GrnController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $panel = $user->role->role_name === 'Manager' ? 'manager' : 'admin';
        return view('admin.grn.index', compact('panel'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $panel = $user->role->role_name === 'Manager' ? 'manager' : 'admin';

        $suppliers = Supplier::where('status', 1)->get();
        $stores = Store::where('status', 1)->get();
        $items = Item::where('status', 1)->get(['id', 'name', 'item_code', 'unit_measure']);

        // Auto-generate GRN Number (e.g., GRN-00001)
        $latestGrn = Grn::latest('id')->first();
        $nextId = $latestGrn ? $latestGrn->id + 1 : 1;
        $grnNo = 'GRN-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        return view('admin.grn.create', compact('panel', 'suppliers', 'stores', 'items', 'grnNo'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('GRN_ADD')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Validate Header
        $request->validate([
            'grn_no' => 'required|unique:grns,grn_no',
            'store_id' => 'required|exists:stores,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'grn_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.cost' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 1. Save GRN Header
            $grn = Grn::create([
                'grn_no' => $request->grn_no,
                'store_id' => $request->store_id,
                'supplier_id' => $request->supplier_id,
                'grn_date' => $request->grn_date,
                'ref_no' => $request->ref_no,
                'note' => $request->note,
                'sub_total' => $request->sub_total,
                'discount_amount' => $request->discount_amount ?? 0,
                'vat_amount' => $request->vat_amount ?? 0,
                'grand_total' => $request->grand_total,
                'created_by' => Auth::id(),
                'status' => 'Completed' // Instant Approval!
            ]);

            // 2. Loop through Items
            foreach ($request->items as $row) {
                $amount = $row['qty'] * $row['cost'];

                // Save GRN Line Item
                GrnItem::create([
                    'grn_id' => $grn->id,
                    'item_id' => $row['item_id'],
                    'qty' => $row['qty'],
                    'cost' => $row['cost'],
                    'amount' => $amount,
                    'batch_exp' => $row['batch_exp'] ?? null
                ]);

                // 3. 🟢 MAGIC: Update Main Inventory Stock & Cost Price
                $item = Item::find($row['item_id']);

                // Add to existing stock
                $currentStock = $item->current_stock ?? 0;
                $newStock = $currentStock + $row['qty'];

                $item->update([
                    'current_stock' => $newStock,
                    'cost_price' => $row['cost'] // Update to Last Purchase Price
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'GRN Saved & Stock Updated Successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ============================================
    // AJAX: Fetch All GRNs
    // ============================================
    public function fetchGrns(Request $request)
    {
        $query = Grn::with(['supplier', 'store', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('grn_no', 'like', "%{$search}%")
                    ->orWhere('ref_no', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $grns = $query->orderBy('id', 'desc')->paginate(10);

        $data = $grns->map(function ($grn) {
            return [
                'id' => $grn->id,
                'grn_no' => $grn->grn_no,
                'supplier_name' => $grn->supplier->name ?? 'N/A',
                'store_name' => $grn->store->name ?? 'N/A',
                'grn_date' => \Carbon\Carbon::parse($grn->grn_date)->format('M d, Y'),
                'ref_no' => $grn->ref_no ?? '-',
                'grand_total' => number_format($grn->grand_total, 2),
                'status' => $grn->status,
                'created_by' => $grn->creator->user_name ?? 'System',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $grns->currentPage(),
                'last_page' => $grns->lastPage(),
                'per_page' => $grns->perPage(),
                'total' => $grns->total(),
                'from' => $grns->firstItem(),
                'to' => $grns->lastItem(),
            ]
        ]);
    }

    // ============================================
    // AJAX: Get Single GRN Details (Header + Items)
    // ============================================
    public function getGrn(int $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('GRN_VIEW')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $grn = Grn::with(['supplier', 'store', 'creator', 'items.item'])->find($id);

        if (!$grn) {
            return response()->json(['success' => false, 'message' => 'GRN not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $grn->id,
                'grn_no' => $grn->grn_no,
                'supplier_name' => $grn->supplier->name ?? 'N/A',
                'store_name' => $grn->store->name ?? 'N/A',
                'grn_date' => \Carbon\Carbon::parse($grn->grn_date)->format('M d, Y'),
                'ref_no' => $grn->ref_no ?? '-',
                'note' => $grn->note ?? 'No notes',
                'sub_total' => number_format($grn->sub_total, 2),
                'discount_amount' => number_format($grn->discount_amount, 2),
                'vat_amount' => number_format($grn->vat_amount, 2),
                'grand_total' => number_format($grn->grand_total, 2),
                'status' => $grn->status,
                'created_by' => $grn->creator->user_name ?? 'System',
                'created_at' => \Carbon\Carbon::parse($grn->created_at)->format('M d, Y h:i A'),
                'items' => $grn->items->map(function ($item) {
                    return [
                        'item_name' => $item->item->name ?? 'Unknown Item',
                        'item_code' => $item->item->item_code ?? '-',
                        'unit' => $item->item->unit_measure ?? 'pcs',
                        'qty' => $item->qty,
                        'cost' => number_format($item->cost, 2),
                        'amount' => number_format($item->amount, 2),
                        'batch_exp' => $item->batch_exp ?? '-',
                    ];
                })
            ]
        ]);
    }
}
