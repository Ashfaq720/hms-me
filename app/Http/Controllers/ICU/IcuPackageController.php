<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuPackage;
use App\Models\Icu\IcuPackageItem;
use App\Services\Icu\PackageCoverageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class IcuPackageController extends Controller
{
    public function index(Request $request)
    {
        $packages = IcuPackage::withCount('items')
            ->when($request->filled('icu_type'), fn($q) => $q->where('icu_type', $request->icu_type))
            ->latest('id')
            ->get();
        return view('icu.packages.index', compact('packages'));
    }

    public function create(Request $request)
    {
        return view('icu.packages.create', [
            'package'    => null,
            'items'      => collect(),
            'categories' => PackageCoverageService::CATEGORIES,
            'icuType'    => $request->input('icu_type'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedPayload($request);
        IcuPackage::create($data);
        $redirect = ['icu_type' => $data['icu_type'] ?? $request->input('icu_type')];
        return redirect()->route('icu.packages.index', array_filter($redirect))
            ->with('success', 'Package created.');
    }

    public function edit($id)
    {
        $package = IcuPackage::with('items')->findOrFail($id);
        return view('icu.packages.create', [
            'package'    => $package,
            'items'      => $package->items,
            'categories' => PackageCoverageService::CATEGORIES,
            'icuType'    => $package->icu_type,
        ]);
    }

    public function update(Request $request, $id)
    {
        $package = IcuPackage::findOrFail($id);
        $data    = $this->validatedPayload($request, $package->id);
        $package->update($data);
        return redirect()->route('icu.packages.edit', $package->id)->with('success', 'Package updated.');
    }

    public function destroy(Request $request, $id)
    {
        $package = IcuPackage::findOrFail($id);
        $unit    = $package->icu_type;
        $package->delete();   // items dropped via separate cleanup if needed
        return redirect()->route('icu.packages.index', array_filter(['icu_type' => $unit]))
            ->with('success', 'Package deleted.');
    }

    /**
     * Add a single rule item (Included / Excluded / Limited) to a package.
     */
    public function addItem(Request $request, $id)
    {
        $request->validate([
            'charge_category'      => ['required', Rule::in(PackageCoverageService::CATEGORIES)],
            'charge_code'          => ['nullable', 'string', 'max:100'],
            'item_name'            => ['nullable', 'string', 'max:200'],
            'rule_type'            => ['required', Rule::in(['Included', 'Excluded', 'Limited'])],
            'included_qty'         => ['nullable', 'integer', 'min:1'],
            'limit_period'         => ['nullable', 'in:PerDay,PerStay'],
            'extra_charge_allowed' => ['nullable', 'boolean'],
        ]);

        IcuPackageItem::create([
            'package_id'           => $id,
            'charge_category'      => $request->charge_category,
            'charge_code'          => $request->charge_code,
            'item_name'            => $request->item_name,
            'rule_type'            => $request->rule_type,
            'included_qty'         => $request->rule_type === 'Limited' ? $request->included_qty : null,
            'limit_period'         => $request->rule_type === 'Limited' ? $request->limit_period : null,
            'extra_charge_allowed' => (bool) $request->input('extra_charge_allowed', true),
        ]);

        return back()->with('success', 'Rule added.');
    }

    public function deleteItem($id, $itemId)
    {
        IcuPackageItem::where('package_id', $id)->where('id', $itemId)->delete();
        return back()->with('success', 'Rule removed.');
    }

    private function validatedPayload(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'package_code' => [
                'required', 'string', 'max:50',
                Rule::unique('icu_packages', 'package_code')->ignore($ignoreId),
            ],
            'package_name' => ['required', 'string', 'max:150'],
            'icu_type'     => ['nullable', Rule::in(['ICU', 'CCU', 'NICU', 'PICU'])],
            'rate'         => ['required', 'numeric', 'min:0'],
            'billing_unit' => ['required', Rule::in(['Day', 'Hour', 'Fixed'])],
            'is_active'    => ['nullable', 'boolean'],
            'description'  => ['nullable', 'string', 'max:1000'],
        ];

        $data = $request->validate($rules);
        $data['is_active'] = (bool) $request->input('is_active', true);
        return $data;
    }
}
