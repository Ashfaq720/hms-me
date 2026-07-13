<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryWarehouse;
use Illuminate\Http\Request;

class InventoryWarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->gate('inventory.warehouse.view');
        $warehouses = InventoryWarehouse::orderBy('name')->paginate(20);
        return view('inventory.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        $this->gate('inventory.warehouse.manage');
        return view('inventory.warehouses.create', ['warehouse' => new InventoryWarehouse()]);
    }

    public function store(Request $request)
    {
        $this->gate('inventory.warehouse.manage');
        $data = $request->validate([
            'code' => 'required|string|max:64|unique:inventory_warehouses,code',
            'name' => 'required|string|max:191',
            'type' => 'required|string|max:32',
            'location' => 'nullable|string|max:191',
            'is_active' => 'nullable|boolean',
        ]);
        $data['organization_id'] = auth()->user()->current_organization_id
            ?? \DB::table('organizations')->value('id');
        $data['branch_id'] = auth()->user()->current_branch_id
            ?? \DB::table('branches')->value('id');
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        InventoryWarehouse::create($data);
        return redirect()->route('inventory.warehouses.index')->with('success', 'Warehouse created.');
    }

    public function show(InventoryWarehouse $warehouse)
    {
        $this->gate('inventory.warehouse.view');
        return view('inventory.warehouses.show', compact('warehouse'));
    }

    public function edit(InventoryWarehouse $warehouse)
    {
        $this->gate('inventory.warehouse.manage');
        return view('inventory.warehouses.create', ['warehouse' => $warehouse]);
    }

    public function update(Request $request, InventoryWarehouse $warehouse)
    {
        $this->gate('inventory.warehouse.manage');
        $data = $request->validate([
            'code' => 'required|string|max:64|unique:inventory_warehouses,code,' . $warehouse->id,
            'name' => 'required|string|max:191',
            'type' => 'required|string|max:32',
            'location' => 'nullable|string|max:191',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $warehouse->update($data);
        return redirect()->route('inventory.warehouses.index')->with('success', 'Warehouse updated.');
    }

    public function destroy(InventoryWarehouse $warehouse)
    {
        $this->gate('inventory.warehouse.manage');
        $warehouse->delete();
        return redirect()->route('inventory.warehouses.index')->with('success', 'Warehouse deleted.');
    }

    private function gate(string $perm): void
    {
        if (! auth()->user()?->can($perm)) {
            abort(403, "Missing permission: {$perm}");
        }
    }
}
