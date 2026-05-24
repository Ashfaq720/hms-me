<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryWarehouse;
use App\Models\Inventory\StockMovement;
use App\Services\Inventory\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockMovementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->gate('inventory.stock.view');

        $movements = StockMovement::query()
            ->with(['item', 'warehouse'])
            ->when($request->integer('item_id'), fn ($q, $id) => $q->where('inventory_item_id', $id))
            ->when($request->integer('warehouse_id'), fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($request->string('reason')->toString(), fn ($q, $r) => $q->where('reason', $r))
            ->latest('performed_at')
            ->paginate(50)
            ->withQueryString();

        $items = InventoryItem::orderBy('name')->limit(500)->get();
        $warehouses = InventoryWarehouse::orderBy('name')->get();

        return view('inventory.movements.index', compact('movements', 'items', 'warehouses'));
    }

    public function create()
    {
        $this->gate('inventory.stock.adjust');
        $items = InventoryItem::orderBy('name')->get();
        $warehouses = InventoryWarehouse::orderBy('name')->get();
        return view('inventory.movements.create', compact('items', 'warehouses'));
    }

    public function store(Request $request, StockLedgerService $ledger)
    {
        $this->gate('inventory.stock.adjust');

        $data = $request->validate([
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'warehouse_id' => ['required', 'exists:inventory_warehouses,id'],
            'direction' => ['required', Rule::in(['in', 'out', 'adjustment_in', 'adjustment_out'])],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['required', Rule::in([
                'grn', 'pharmacy_dispense', 'ipd_dispense', 'opd_dispense',
                'ot_consumption', 'icu_consumption', 'lab_consumption',
                'return', 'damaged', 'expired', 'transfer', 'stock_count', 'opening', 'other',
            ])],
            'batch_no' => ['nullable', 'string', 'max:64'],
            'expiry_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $data['organization_id'] = auth()->user()->current_organization_id;
        $data['branch_id'] = auth()->user()->current_branch_id;

        $ledger->record($data);

        return redirect()->route('inventory.movements.index')
            ->with('success', 'Stock movement recorded.');
    }

    private function gate(string $perm): void
    {
        if (! auth()->user()?->can($perm)) {
            abort(403, "Missing permission: {$perm}");
        }
    }
}
