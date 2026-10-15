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
use Illuminate\Support\Facades\Cache; // Added Cache Facade

class GrnController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $panel = strtolower($user->role->role_name ?? 'admin');

        return view('admin.grn.index', compact('panel'));
    }

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
            $query->where('grns.status', $request->status);
        }

        if ($request->filled('grn_type') && $request->grn_type !== 'all') {
            $query->where('grns.grn_type', $request->grn_type);
        }

        $grns = $query->orderBy('id', 'desc')->paginate(10);

        $data = $grns->map(function (Grn $grn) {
            return [
                'id' => $grn->id,
                'grn_no' => $grn->grn_no,
                'grn_type' => $grn->grn_type,
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

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $panel = strtolower($user->role->role_name ?? 'admin');

        $suppliers = Supplier::where('status', 1)->get();
        $stores = Store::where('status', 1)->get();

        $items = Item::where('status', 1)->get([
            'id',
            'name',
            'item_code',
            'unit_measure',
            'stock_type',
            'cost_price',
            'sales_price'
        ]);

        // 🛡️ SECURITY FIX 1: Do not generate GRN No on page load!
        // If 2 managers open the page at the same time, they would get the same number.
        // We will show "[Auto Generated]" on the UI and generate it on save.
        $grnNo = '[Auto Generated]';

        return view('admin.grn.create', compact('panel', 'suppliers', 'stores', 'items', 'grnNo'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasOptionPermission('GRN_ADD')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // 🛡️ SECURITY FIX 2: Atomic Cache Lock (Idempotency)
        // Prevents double-submission from network lag or impatient double-clicking.
        // Locks this specific user from submitting another GRN for 10 seconds.
        $lock = Cache::lock('submit_grn_user_' . Auth::id(), 10);

        if (!$lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'A GRN submission is already processing. Please wait.'
            ], 429); // 429 Too Many Requests
        }

        // Validate
        $request->validate([
            'grn_type' => 'required|in:Stock GRN,Non-Stock GRN',
            'store_id' => 'required|exists:stores,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'grn_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.cost' => 'required|numeric|min:0',
            'items.*.sales_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 🛡️ SECURITY FIX 3: Generate GRN Number securely inside the transaction
            // We lock the GRN table rows temporarily to prevent concurrent creation overlaps
            $latestGrn = Grn::lockForUpdate()->latest('id')->first();
            $nextId = $latestGrn ? $latestGrn->id + 1 : 1;
            $secureGrnNo = 'GRN-' . str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);

            // 1. Save GRN Header
            $grn = Grn::create([
                'grn_no' => $secureGrnNo,
                'grn_type' => $request->grn_type,
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
                'status' => 'Completed'
            ]);

            // 2. Loop through Items
            foreach ($request->items as $row) {
                $amount = $row['qty'] * $row['cost'];

                GrnItem::create([
                    'grn_id' => $grn->id,
                    'item_id' => $row['item_id'],
                    'qty' => $row['qty'],
                    'cost' => $row['cost'],
                    'sales_price' => $row['sales_price'],
                    'amount' => $amount,
                    'batch_exp' => $row['batch_exp'] ?? null
                ]);

                // 3. Update Item Master
                $item = Item::lockForUpdate()->find($row['item_id']); // Lock item row during stock update

                if ($item) {
                    $updateData = [
                        'cost_price' => $row['cost'],
                        'sales_price' => $row['sales_price']
                    ];

                    if ($request->grn_type === 'Stock GRN') {
                        $currentStock = $item->current_stock ?? 0;
                        $updateData['current_stock'] = $currentStock + $row['qty'];
                    }

                    $item->update($updateData);
                }
            }

            DB::commit();
            $lock->release(); // Release the lock on success

            return response()->json([
                'success' => true,
                'message' => 'GRN ' . $secureGrnNo . ' Saved & Stock Updated Successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $lock->release(); // Release the lock if error occurs so they can try again

            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

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
                'grn_type' => $grn->grn_type,
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
                'items' => $grn->items->map(function (GrnItem $item) {
                    return [
                        'item_name' => $item->item->name ?? 'Unknown Item',
                        'item_code' => $item->item->item_code ?? '-',
                        'unit' => $item->item->unit_measure ?? 'pcs',
                        'qty' => $item->qty,
                        'cost' => number_format($item->cost, 2),
                        'sales_price' => number_format((float)$item->sales_price, 2),
                        'amount' => number_format($item->amount, 2),
                        'batch_exp' => $item->batch_exp ?? '-',
                    ];
                })
            ]
        ]);
    }
}
