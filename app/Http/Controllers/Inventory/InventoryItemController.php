<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->gate('inventory.item.view');

        // Honor user-selected page size (default 25, "all" = unbounded)
        $perPage = $request->input('per_page', 25);
        $perPage = in_array((string) $perPage, ['10', '25', '50', '100', 'all'], true) ? $perPage : 25;

        // The sidebar quick-filter links use `?type=medicine|consumable|asset`.
        // Translate that into the right column filter so menu navigation matches the data.
        $type = $request->string('type')->toString();
        $typeMap = [
            'medicine'   => ['category', 'medicine'],
            'consumable' => ['category', 'consumable'],
            'asset'      => ['is_asset', 1],          // equipment / fixed assets
            'equipment'  => ['is_asset', 1],
            'implant'    => ['category', 'implant'],
            'reagent'    => ['category', 'reagent'],
        ];

        $query = InventoryItem::query()
            ->when($request->string('q')->toString(), function ($q, $term) {
                $q->where(function ($w) use ($term) {
                    $w->where('name', 'like', "%$term%")
                        ->orWhere('code', 'like', "%$term%")
                        ->orWhere('generic_name', 'like', "%$term%")
                        ->orWhere('brand', 'like', "%$term%");
                });
            })
            ->when($request->string('category')->toString(), fn ($q, $c) => $q->where('category', $c))
            ->when($type && isset($typeMap[$type]), function ($q) use ($type, $typeMap) {
                [$col, $val] = $typeMap[$type];
                $q->where($col, $val);
            })
            ->orderBy('name');

        $total = (clone $query)->count();
        $items = $perPage === 'all'
            ? $query->paginate(max($total, 1))->withQueryString()
            : $query->paginate((int) $perPage)->withQueryString();

        $categories = InventoryItem::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        return view('inventory.items.index', compact('items', 'total', 'perPage', 'categories'));
    }

    public function create()
    {
        $this->gate('inventory.item.manage');
        return view('inventory.items.create', ['item' => new InventoryItem()]);
    }

    public function store(Request $request)
    {
        $this->gate('inventory.item.manage');
        $data = $this->validated($request);
        InventoryItem::create($data);
        return redirect()->route('inventory.items.index')
            ->with('success', 'Inventory item created.');
    }

    public function show(InventoryItem $item)
    {
        $this->gate('inventory.item.view');
        $item->load(['batches.warehouse', 'movements' => fn ($q) => $q->latest('performed_at')->limit(20)]);
        return view('inventory.items.show', compact('item'));
    }

    public function edit(InventoryItem $item)
    {
        $this->gate('inventory.item.manage');
        return view('inventory.items.edit', compact('item'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $this->gate('inventory.item.manage');
        $data = $this->validated($request, $item);
        $item->update($data);
        return redirect()->route('inventory.items.show', $item)
            ->with('success', 'Inventory item updated.');
    }

    public function destroy(InventoryItem $item)
    {
        $this->gate('inventory.item.manage');
        $item->delete();
        return redirect()->route('inventory.items.index')
            ->with('success', 'Inventory item archived.');
    }

    private function validated(Request $request, ?InventoryItem $item = null): array
    {
        return $request->validate([
            'code' => [
                'required', 'string', 'max:64',
                Rule::unique('inventory_items')->ignore($item?->id),
            ],
            'name' => ['required', 'string', 'max:191'],
            'category' => ['nullable', 'string', 'max:64'],
            'generic_name' => ['nullable', 'string', 'max:191'],
            'brand' => ['nullable', 'string', 'max:191'],
            'sku' => ['nullable', 'string', 'max:64'],
            'barcode' => ['nullable', 'string', 'max:64'],
            'uom' => ['required', 'string', 'max:32'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'reorder_quantity' => ['nullable', 'numeric', 'min:0'],
            'storage_condition' => ['nullable', 'string', 'max:191'],
            'is_controlled' => ['nullable', 'boolean'],
            'is_consumable' => ['nullable', 'boolean'],
            'is_asset' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function gate(string $permission): void
    {
        if (! auth()->user()?->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
